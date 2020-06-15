<?php

namespace App\MessageHandler;

use App\Entity\ImportData;
use App\Message\ImportDataFileUploaded;
use App\Repository\ImportDataRepository;
use App\Service\DataImport\ImportDataManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class ImportDataFileUploadedHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ImportDataFileUploadedHandler implements MessageHandlerInterface
{
    /**
     * @var ImportDataRepository
     */
    private $repository;

    /**
     * @var ImportDataManager
     */
    private $importDataManager;

    /**
     * ImportDataFileUploadedHandler constructor.
     *
     * @param ImportDataRepository $repository
     * @param ImportDataManager    $importDataManager
     */
    public function __construct(ImportDataRepository $repository, ImportDataManager $importDataManager)
    {
        $this->repository = $repository;
        $this->importDataManager = $importDataManager;
    }

    /**
     *
     * @param ImportDataFileUploaded $message
     *
     * @return void
     *
     * @throws \League\Csv\Exception
     */
    public function __invoke(ImportDataFileUploaded $message)
    {
        /* @var ImportData $entity */
        $entity = $this->repository->find($message->getId());
        if (!$entity || $entity->isImported()) {
            return;
        }

        foreach ($this->importDataManager->import($entity) as $row) {
            $row;
        }
    }
}
