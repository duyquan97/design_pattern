<?php

namespace App\Command;

use App\Message\Factory\PullRoomFactory;
use App\Model\PartnerInterface;
use App\Repository\PartnerRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class UpdateRoomsCommand
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class UpdateRoomsCommand extends Command
{
    public const PARTNER_OPTION = 'partner';
    public const ALL_OPTION = 'all';

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var PullRoomFactory
     */
    private $messageFactory;

    /**
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     * UpdateRoomsCommand constructor.
     *
     * @param MessageBusInterface $messageBus
     * @param PullRoomFactory $messageFactory
     * @param PartnerRepository $partnerRepository
     */
    public function __construct(MessageBusInterface $messageBus, PullRoomFactory $messageFactory, PartnerRepository $partnerRepository)
    {
        $this->messageBus = $messageBus;
        $this->messageFactory = $messageFactory;
        $this->partnerRepository = $partnerRepository;

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $partnerIds = $input->getOption(self::PARTNER_OPTION);
        if ($partnerIds) {
            $partnerIds = explode(',', $partnerIds);
        }

        if ($input->getOption(self::ALL_OPTION)) {
            $partnerIds = [];
        }

        $partners = $this->partnerRepository->iterate($partnerIds ?: []);
        $totalPartners = $this->partnerRepository->countByIdentifiers($partnerIds ?: []);
        $output->writeln("$totalPartners partners to process");
        $progress = new ProgressBar($output, $totalPartners);
        $progress->start();

        foreach ($partners as $partner) {
            /** @var PartnerInterface $partner */
            $partner = $partner[0];
            $this->messageBus->dispatch(
                $this->messageFactory->create($partner->getIdentifier())
            );

            $progress->advance();
        }

        $progress->finish();
        $output->writeln('');
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cmhub:products:pull')
            ->addOption(self::ALL_OPTION, 'a', InputOption::VALUE_NONE, 'pull rooms for all partners')
            ->addOption(self::PARTNER_OPTION, 'p', InputOption::VALUE_REQUIRED, 'List of partner identifier, separated by comma.')
            ->setDescription('Synchronize rooms between CMHUB and iResa. Example :
            php bin/console cmhub:products:pull --partner=123123,314432');
    }
}
