<?php

namespace App\Repository\Listener;

use App\Entity\ImportData;
use App\Message\Factory\ImportDataFileUploadedFactory;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class BookingListener
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ImportDataListener
{
    /**
     * @var MessageBusInterface $bus
     */
    protected $bus;

    /**
     * @var ImportDataFileUploadedFactory
     */
    protected $messageFactory;

    /**
     * ImportDataListener constructor.
     *
     * @param MessageBusInterface           $bus
     * @param ImportDataFileUploadedFactory $messageFactory
     */
    public function __construct(MessageBusInterface $bus, ImportDataFileUploadedFactory $messageFactory)
    {
        $this->bus = $bus;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param ImportData $importData
     *
     * @return void
     */
    public function postPersist(ImportData $importData): void
    {
        $this->bus->dispatch($this->messageFactory->create($importData->getId()));
    }
}
