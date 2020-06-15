<?php

namespace App\Command;

use App\Entity\ImportData;
use App\Service\DataImport\ImportDataManager;
use App\Service\DataImport\Model\ImportDataRowInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ImportDataCommand
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ImportDataCommand extends Command
{
    public const OPTION_BATCH = 'batch';
    public const OPTION_CONTINUE = 'continue';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ImportDataManager
     */
    private $importDataManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * ImportDataCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ImportDataManager      $importDataManager
     * @param Filesystem             $filesystem
     */
    public function __construct(EntityManagerInterface $entityManager, ImportDataManager $importDataManager, Filesystem $filesystem)
    {
        $this->entityManager = $entityManager;
        $this->importDataManager = $importDataManager;
        $this->filesystem = $filesystem;

        parent::__construct();
    }

    /**
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cmhub:data:import')
            ->addOption(self::OPTION_BATCH, 'b', InputOption::VALUE_REQUIRED, 'Insert/update batches', 100)
            ->addOption(self::OPTION_CONTINUE, 'c', InputOption::VALUE_NONE, 'Continue from last processed row');
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
        $imports = $this->entityManager->getRepository(ImportData::class)->findBy(['imported' => false]);

        $output->writeln(sprintf('Found %s files to import', count($imports)));

        $batchSize = $input->getOption(self::OPTION_BATCH);
        $output->writeln('Batch size: ' . $batchSize);

        foreach ($imports as $import) {
            if (!$this->filesystem->exists($import->getPath())) {
                $output->writeln(
                    [
                        '',
                        sprintf('The file %s does not exist', $import->getFilename()),
                    ]
                );

                continue;
            }

            $index = 0;
            try {
                $this->filesystem->touch($import->getPath() . '.errors');
                $output->writeln(
                    [
                        '',
                        sprintf('Importing %s - File %s', $import->getType(), $import->getFilename()),
                    ]
                );

                $offset = 0;
                if ($input->getOption(self::OPTION_CONTINUE)) {
                    $offset = $index = $import->getProcessedRows();
                }

                $records = $this->importDataManager->count($import, $offset);
                $progress = new ProgressBar($output, $records);
                $progress->start();
                /* @var ImportDataRowInterface $row */
                foreach ($this->importDataManager->import($import, $offset) as $row) {
                    $output->writeln($row->hasException());
                    $progress->advance();
                }

                $progress->finish();
            } catch (\Exception $exception) {
                $output->writeln(sprintf('An error ocurred processing the %s batch: %s', $index, $exception->getMessage()));
            }
        }
    }
}
