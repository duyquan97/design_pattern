<?php

namespace App\Command;

use App\Entity\Transaction;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Repository\TransactionRepository;
use App\Service\Broadcaster\BroadcastManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BroadcastDataCommand
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BroadcastDataCommand extends Command
{
    public const AVAILABILITY_OPTION = 'availability';
    public const RATES_OPTION = 'price';
    public const BOOKINGS_OPTION = 'booking';
    public const LIMIT_OPTION = 'limit';
    public const STATUS_OPTION = 'status';
    public const CHANNEL_OPTION = 'channel';

    /**
     * @var BroadcastManager
     */
    private $broadcastManager;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * BroadcastDataCommand constructor.
     *
     * @param BroadcastManager       $broadcastManager
     * @param TransactionRepository  $transactionRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(BroadcastManager $broadcastManager, TransactionRepository $transactionRepository, EntityManagerInterface $entityManager)
    {
        $this->broadcastManager = $broadcastManager;
        $this->transactionRepository = $transactionRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }


    /**
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cmhub:data:broadcast')
            ->addOption(self::AVAILABILITY_OPTION, 'a', InputOption::VALUE_NONE, 'Broadcast scheduled availability.')
            ->addOption(self::RATES_OPTION, 'p', InputOption::VALUE_NONE, 'Broadcast scheduled rates.')
            ->addOption(self::BOOKINGS_OPTION, 'b', InputOption::VALUE_NONE, 'Broadcast scheduled bookings.')
            ->addOption(self::LIMIT_OPTION, 'l', InputOption::VALUE_REQUIRED, 'Limit the number of transaction to be process.')
            ->addOption(self::STATUS_OPTION, 's', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Specify which type of transaction to be sent based on their status eg: "failed", "error", "scheduled". If nothing provided, only "scheduled" ones get processed.')
            ->addOption(self::CHANNEL_OPTION, 'c', InputOption::VALUE_REQUIRED, 'Select channel to broadcast eg: "eai", "iresa". If no channel provided, all channels processed')
            ->setDescription('Broadcast scheduled transactions to external channel such as: iResa, Channel Managers. Example :
            php bin/console cmhub:data:broadcast --availability --bookings --max=100 --status=failed --status=scheduled --channel=iresa');
    }

    /**
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $channel = $input->getOption(self::CHANNEL_OPTION);
        $types = [];
        if ($input->getOption(self::AVAILABILITY_OPTION)) {
            $types[] = TransactionType::AVAILABILITY;
        }

        if ($input->getOption(self::RATES_OPTION)) {
            $types[] = TransactionType::PRICE;
        }

        if ($input->getOption(self::BOOKINGS_OPTION)) {
            $types[] = TransactionType::BOOKING;
        }

        $statuses = $input->getOption(self::STATUS_OPTION);

        if ($statuses) {
            foreach ($statuses as $status) {
                if (!in_array($status, TransactionStatus::ALL)) {
                    $output->writeln(sprintf('<error>The status "%s" is not supported. Available options are : "error", "scheduled", "failed"</error>', $status));

                    return;
                }
            }
        }

        if (empty($statuses)) {
            $statuses[] = TransactionStatus::SCHEDULED;
        }

        $limit = $input->getOption(self::LIMIT_OPTION);

        if ($limit) {
            $limit = (int) $limit;
            // if limit is invalid number, ignore it
            if ($limit <= 0) {
                $limit = null;
            }
        }

        $transactions = $this->transactionRepository->findByTypesAndStatusesAndChannel($types, $statuses, $limit, $channel);
        $totalTransaction = $this->transactionRepository->countByTypesAndStatusAndChannel($types, $statuses, $channel);

        $output->writeln(sprintf('<comment>%d transactions found</comment>', $totalTransaction));

        if (0 === $totalTransaction) {
            return;
        }

        if ($limit && $totalTransaction > $limit) {
            $totalTransaction = $limit;
        }

        $progress = new ProgressBar($output, $totalTransaction);
        $progress->start();

        $succeeded = 0;
        /* @var Transaction $transaction */
        foreach ($this->broadcast($transactions) as $transaction) {
            if (!$transaction) {
                continue;
            }

            if ($transaction->isSuccess()) {
                $succeeded++;
            }

            $this->entityManager->clear();

            $progress->advance();
        }

        $progress->finish();
        $output->writeln(
            [
                '',
                sprintf('<success>%d transactions successfully sent out of %d</success>', $succeeded, $totalTransaction),
            ]
        );
    }

    /**
     *
     * @param IterableResult $transactions
     *
     * @return \Generator
     */
    private function broadcast(IterableResult $transactions)
    {
        /**
         * @var array $data
         */
        foreach ($transactions as $data) {
            try {
                $transaction = $this->broadcastManager->broadcast(current($data));
                yield $transaction;
            } catch (\Exception $exception) {
                $this->output->writeln('Exception: ' . $exception->getMessage());
                yield null;
            }
        }
    }
}
