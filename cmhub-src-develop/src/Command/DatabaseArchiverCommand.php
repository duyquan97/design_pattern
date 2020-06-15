<?php

namespace App\Command;

use App\Service\Archives\DatabaseArchive;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DatabaseArchiverCommand
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class DatabaseArchiverCommand extends Command
{
    protected static $defaultName = 'cmhub:data:archive';
    private const OPTION_TABLE = 'table';
    private const ARCHIVING_MESSAGE = 'Archiving data from table %s';

    /**
     * @var DatabaseArchive[]
     */
    private $databaseArchive;

    /**
     * BackUpDatabaseCommand constructor.
     *
     * @param array $databaseArchive
     */
    public function __construct(array $databaseArchive)
    {
        $this->databaseArchive = $databaseArchive;

        parent::__construct();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $input->getOption(self::OPTION_TABLE);

        foreach ($this->databaseArchive as $databaseArchive) {
            try {
                if (!$table) {
                    $output->writeln(sprintf(self::ARCHIVING_MESSAGE, $databaseArchive->getTableSource()));
                    $databaseArchive->archive();

                    continue;
                }

                if ($databaseArchive->getTableSource() === $table) {
                    $output->writeln(sprintf(self::ARCHIVING_MESSAGE, $databaseArchive->getTableSource()));
                    $databaseArchive->archive();
                }
            } catch (\Exception $exception) {
                $output->writeln($exception->getMessage());
            }
        }
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->addOption(static::OPTION_TABLE, 't', InputOption::VALUE_OPTIONAL, 'The table name containing the data to archive')
            ->setDescription('Archives data moving the data from source table to archive tables. Archive tables are defined in services.');
    }
}
