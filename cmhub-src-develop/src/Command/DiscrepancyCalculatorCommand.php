<?php

namespace App\Command;

use App\Model\ProductInterface;
use App\Utils\Monolog\LogAction;
use App\Utils\Monolog\LogKey;
use Monolog\Logger;
use App\Entity\Partner;
use App\Exception\DateFormatException;
use App\Model\PartnerInterface;
use App\Repository\PartnerRepository;
use App\Service\Synchronizer\Diff\AvailabilityDiff;
use App\Service\Synchronizer\Diff\PriceDiff;
use App\Utils\Monolog\CmhubLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DiscrepancyCalculatorCommand
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class DiscrepancyCalculatorCommand extends Command
{
    public const START_OPTION = 'start';
    public const END_OPTION = 'end';
    public const PARTNER_OPTION = 'partner';
    private const THREE_YEAR_INTERVAL = '+3 years';
    public const PERIOD_OPTION = 'period';

    /**
     * @var AvailabilityDiff
     */
    private $availabilityDiff;

    /**
     * @var PriceDiff
     */
    private $priceDiff;

    /**
     *
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CmhubLogger
     */
    private $logger;

    /**
     * DiscrepancyCalculatorCommand constructor.
     *
     * @param AvailabilityDiff $availabilityDiff
     * @param PriceDiff $priceDiff
     * @param EntityManagerInterface $entityManager
     * @param CmhubLogger $logger
     */
    public function __construct(AvailabilityDiff $availabilityDiff, PriceDiff $priceDiff, EntityManagerInterface $entityManager, CmhubLogger $logger)
    {
        $this->availabilityDiff = $availabilityDiff;
        $this->priceDiff = $priceDiff;
        $this->entityManager = $entityManager;
        $this->partnerRepository = $this->entityManager->getRepository(Partner::class);
        $this->logger = $logger;

        parent::__construct();
    }


    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     *
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $start = (new \DateTime())->setTime(0, 0);
        $end = (clone $start)->modify(self::THREE_YEAR_INTERVAL);
        if ($period = $input->getOption(self::PERIOD_OPTION)) {
            if (!$end = date_create($period)) {
                $output->writeln(sprintf('<error>Invalid date period %s</error>', $period));

                return;
            }
        }

        if ($input->getOption(self::START_OPTION)) {
            $start = $this->getDate($input, self::START_OPTION);
        }

        if ($input->getOption(self::END_OPTION)) {
            $end = $this->getDate($input, self::END_OPTION);
        }

        if ($end < $start) {
            throw new \LogicException('Start date cant be greater than end date');
        }

        $partnerIds = $input->getOption(self::PARTNER_OPTION);
        if ($partnerIds) {
            $partnerIds = explode(',', $partnerIds);
        }

        $partners = $this->partnerRepository->iterate($partnerIds ?: []);
        $totalPartners = $this->partnerRepository->countByIdentifiers($partnerIds ?: []);
        $output->writeln("$totalPartners to process");
        $output->writeln('');
        $progress = new ProgressBar($output, $totalPartners);
        $progress->start();
        foreach ($partners as $partner) {
            $availabilityDiscrepancies = $priceDiscrepancies = 0;
            /** @var PartnerInterface $partner */
            $partner = $partner[0];
            try {
                $availabilities = $this->calculateAvailabilityDiscrepancy($partner, $start, $end);
                foreach ($availabilities as $id => $count) {
                    $this->addDiscrepancyReport($partner, $id, LogAction::DISCREPANCY_AVAILABILITY, $count, $start, $end);
                    $availabilityDiscrepancies += $count;
                }
            } catch (\Exception $exception) {
                $this->addDiscrepancyReport($partner, null, LogAction::DISCREPANCY_AVAILABILITY, 0, $start, $end, $exception);
            }

            try {
                $rates = $this->calculatePriceDiscrepancy($partner, $start, $end);
                foreach ($rates as $id => $count) {
                    $this->addDiscrepancyReport($partner, $id, LogAction::DISCREPANCY_PRICE, $count, $start, $end);
                    $priceDiscrepancies += $count;
                }
            } catch (\Exception $exception) {
                $this->addDiscrepancyReport($partner, null, LogAction::DISCREPANCY_PRICE, 0, $start, $end, $exception);
            }

            $output->writeln(
                sprintf(
                    'There are %s availability and %s price discrepancies for partner %s(%s) between %s and %s',
                    $availabilityDiscrepancies,
                    $priceDiscrepancies,
                    $partner->getName(),
                    $partner->getIdentifier(),
                    $start->format('Y-m-d'),
                    $end->format('Y-m-d'),
                )
            );

            $this->entityManager->clear();
            $progress->advance();
        }
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cmhub:discrepancy:build')
            ->addOption(self::PARTNER_OPTION, 't', InputOption::VALUE_REQUIRED, 'List of partner identifier, separated by comma.')
            ->addOption(self::START_OPTION, 's', InputOption::VALUE_REQUIRED, 'Start date in format of Y-m-d, eg : 2019-09-01')
            ->addOption(self::END_OPTION, 'ed', InputOption::VALUE_REQUIRED, 'End date in format of Y-m-d, eg: 2019-09-01')
            ->addOption(self::PERIOD_OPTION, 'r', InputOption::VALUE_REQUIRED, 'Date period, eg: +1 year, +2 years')
            ->setDescription('Calculate discrepancy in availability, price between CMHUB and iResa. Example :
            php bin/console cmhub:discrepancy:build --partner=123123,314432 --start=2019-10-02 --end=2020-11-01');
    }

    /**
     * @param InputInterface $input
     * @param string         $option
     *
     * @return \DateTime
     *
     * @throws DateFormatException
     */
    private function getDate(InputInterface $input, string $option): \DateTime
    {
        $start = \DateTime::createFromFormat(
            '!Y-m-d',
            $input->getOption($option)
        );

        if (false === $start) {
            throw new DateFormatException('Y-m-d');
        }

        return $start;
    }

    /**
     * @param PartnerInterface $partner
     * @param string|null $productId
     * @param string $type
     * @param int $amount
     * @param \DateTime $start
     * @param \DateTime $end
     * @param \Exception|null $exception
     *
     * @return void
     */
    private function addDiscrepancyReport(PartnerInterface $partner, ?string $productId, string $type, int $amount, \DateTime $start, \DateTime $end, \Exception $exception = null)
    {
        $message = 'Discrepancy report';
        $level = Logger::INFO;
        $status = 'success';

        if (null !== $exception) {
            $message = $exception->getMessage();
            $level = Logger::ERROR;
            $status = 'failed';
        }

        $this->logger->addRecord(
            $level,
            $message,
            [
                LogKey::TYPE_KEY => 'discrepancy',
                'report_type' => $type,
                LogKey::QUANTITY_KEY => $amount,
                LogKey::PARTNER_ID_KEY => $partner->getIdentifier(),
                LogKey::PARTNER_NAME_KEY => $partner->getName(),
                LogKey::PRODUCT_ID_KEY => null !== $productId ? $productId : '',
                LogKey::START_DATE => $start->format('Y-m-d'),
                LogKey::END_DATE => $end->format('Y-m-d'),
                LogKey::CM_KEY => ($cm = $partner->getChannelManager()) ? $cm->getIdentifier() : '',
                LogKey::STATUS_KEY => $status,
                LogKey::MESSAGE_KEY => $message,
            ],
        );
    }

    /**
     * @param PartnerInterface $partner
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return array
     *
     * @throws \App\Exception\CmHubException
     */
    private function calculateAvailabilityDiscrepancy(PartnerInterface $partner, \DateTime $start, \DateTime $end)
    {
        $data = [];
        do {
            $currentDate = (clone $start)->modify('+1 month');
            if ($currentDate > $end) {
                $currentDate = $end;
            }

            $availabilities = $this->availabilityDiff->diff($partner, $start, $currentDate);
            foreach ($availabilities as $productAvailability) {
                if (!array_key_exists($productAvailability->getProduct()->getIdentifier(), $data)) {
                    $data[$productAvailability->getProduct()->getIdentifier()] = 0;
                }

                $data[$productAvailability->getProduct()->getIdentifier()] += count($productAvailability->getAvailabilities());
            }

            $start = (clone $currentDate)->modify('+1 day');
        } while ($currentDate < $end);

        return $data;
    }

    /**
     * @param PartnerInterface $partner
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return array
     *
     * @throws \App\Exception\CmHubException
     */
    private function calculatePriceDiscrepancy(PartnerInterface $partner, \DateTime $start, \DateTime $end)
    {
        $data = [];
        do {
            $currentDate = (clone $start)->modify('+1 month');
            if ($currentDate > $end) {
                $currentDate = $end;
            }

            $productRates = $this->priceDiff->diff($partner, $start, $currentDate);
            foreach ($productRates as $productRate) {
                if (!array_key_exists($productRate->getProduct()->getIdentifier(), $data)) {
                    $data[$productRate->getProduct()->getIdentifier()] = 0;
                }

                $data[$productRate->getProduct()->getIdentifier()] += count($productRate->getRates());
            }

            $start = (clone $currentDate)->modify('+1 day');
        } while ($currentDate < $end);

        return $data;
    }
}
