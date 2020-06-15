<?php

namespace App\Command;

use App\Exception\DateFormatException;
use App\Message\Factory\SyncDataFactory;
use App\Model\PartnerInterface;
use App\Repository\PartnerRepository;
use App\Service\Synchronizer\AvailabilityForcedAlignment;
use App\Service\Synchronizer\PriceForcedAlignment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class SynchronizeCommand
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SynchronizeCommand extends Command
{
    public const START_OPTION = 'start';
    public const END_OPTION = 'end';
    public const PRICE_OPTION = 'price';
    public const AVAILABILITY_OPTION = 'availability';
    public const PARTNER_OPTION = 'partner';
    public const BATCH_OPTION = 'batch';
    public const PERIOD_OPTION = 'period';
    public const LIMIT_OPTION = 'limit';
    public const OFFSET_OPTION = 'offset';

    private const ONE_YEAR_INTERVAL = '+1 year';
    private const BATCH_SIZE = 200;

    /**
     *
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     *
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     *
     * @var SyncDataFactory
     */
    private $messageFactory;

    /**
     * SynchronizeCommand constructor.
     *
     * @param PartnerRepository          $partnerRepository
     * @param MessageBusInterface        $messageBus
     * @param SyncDataFactory            $messageFactory
     */
    public function __construct(PartnerRepository $partnerRepository, MessageBusInterface $messageBus, SyncDataFactory $messageFactory)
    {
        $this->partnerRepository = $partnerRepository;
        $this->messageBus = $messageBus;
        $this->messageFactory = $messageFactory;

        parent::__construct();
    }

    /**
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     *
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $myfile = fopen(__DIR__ . '/../../public/sync.errors', 'w+');

        try {
            if ($batch = $input->getOption(self::BATCH_OPTION)) {
                $output->writeln(sprintf('Batch size: %s', $batch));
            }

            $start = new \DateTime();
            $end = new \DateTime(self::ONE_YEAR_INTERVAL);
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

            if ($availability = $input->getOption(self::AVAILABILITY_OPTION)) {
                $output->writeln(sprintf('Align availabilities from %s to %s', $start->format('Y-m-d'), $end->format('Y-m-d')));
            }

            if ($price = $input->getOption(self::PRICE_OPTION)) {
                $output->writeln(sprintf('Align prices from %s to %s', $start->format('Y-m-d'), $end->format('Y-m-d')));
            }

            $partnerIds = $input->getOption(self::PARTNER_OPTION);
            if ($partnerIds) {
                $partnerIds = explode(',', $partnerIds);
            }

            $limit = $input->getOption(self::LIMIT_OPTION);
            $offset = $input->getOption(self::OFFSET_OPTION);

            $totalPartners = $this->partnerRepository->countByIdentifiers($partnerIds ?: []);

            $output->writeln("$totalPartners partners to process");
            if (null !== $limit && null !== $offset) {
                $limit = intval($limit);
                $offset = intval($offset);
                if ($totalPartners <= $offset || $limit <= 0 || $offset < 0) {
                    $output->writeln("<error>Invalid limit and offset</error>");

                    return;
                }

                $totalPartners = $totalPartners - $offset;
                if ($totalPartners > $limit) {
                    $totalPartners = $limit;
                }

                $output->writeln("<info>Process $totalPartners partners from position $offset</info>");
            }
            $output->writeln('');

            $partners = $this->partnerRepository->iterate($partnerIds ?: [], $limit, $offset);

            $progress = new ProgressBar($output, $totalPartners);
            $progress->start();

            foreach ($partners as $partner) {
                /** @var PartnerInterface $partner */
                $partner = $partner[0];
                if ($availability) {
                    $this->messageBus->dispatch($this->messageFactory->create($partner->getIdentifier(), AvailabilityForcedAlignment::TYPE, $start, $end));
                }

                if ($price) {
                    $this->messageBus->dispatch($this->messageFactory->create($partner->getIdentifier(), PriceForcedAlignment::TYPE, $start, $end));
                }

                $progress->advance();
            }

            fclose($myfile);
            $progress->finish();
            $output->writeln('');
        } catch (\Throwable $exception) {
            $output->writeln($exception->getMessage());
            fwrite($myfile, sprintf("%s,%s\r\n", '', $exception->getMessage()));
            fclose($myfile);
        }
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cmhub:data:build')
            ->addOption(self::AVAILABILITY_OPTION, 'a', InputOption::VALUE_NONE, 'synchronize availability data.')
            ->addOption(self::BATCH_OPTION, 'b', InputOption::VALUE_OPTIONAL, 'Batch size', static::BATCH_SIZE)
            ->addOption(self::PRICE_OPTION, 'p', InputOption::VALUE_NONE, 'synchronize price data.')
            ->addOption(self::PARTNER_OPTION, 't', InputOption::VALUE_REQUIRED, 'List of partner identifier, separated by comma.')
            ->addOption(self::START_OPTION, 's', InputOption::VALUE_REQUIRED, 'Start date in format of Y-m-d, eg : 2019-09-01')
            ->addOption(self::END_OPTION, 'ed', InputOption::VALUE_REQUIRED, 'End date in format of Y-m-d, eg: 2019-09-01')
            ->addOption(self::PERIOD_OPTION, 'r', InputOption::VALUE_REQUIRED, 'Date period, eg: +1 year, +2 years')
            ->addOption(self::LIMIT_OPTION, 'l', InputOption::VALUE_REQUIRED, 'Limit option to restrict the number of partner to be processed in a loop')
            ->addOption(self::OFFSET_OPTION, 'o', InputOption::VALUE_REQUIRED, 'Offset option to specify partners to be processed in the loop')
            ->setDescription('Synchronize data (availability, price ..) between CMHUB and iResa. Example :
            php bin/console cmhub:data:build --availability --partner=123123,314432 --start=2019-10-02 --end=2020-11-01');
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
            'Y-m-d',
            $input->getOption($option)
        );

        if (false === $start) {
            throw new DateFormatException('Y-m-d');
        }

        return $start;
    }
}
