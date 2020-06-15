<?php

namespace spec\App\MessageHandler;

use App\Entity\ImportData;
use App\Message\ImportDataFileUploaded;
use App\MessageHandler\ImportDataFileUploadedHandler;
use App\Repository\ImportDataRepository;
use App\Service\DataImport\ImportDataManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ImportDataFileUploadedHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ImportDataFileUploadedHandler::class);
    }

    function let(
        ImportDataRepository $repository,
        ImportDataManager $importDataManager
    )
    {
        $this->beConstructedWith($repository, $importDataManager);
    }

    function it_process_message(
        ImportDataRepository $repository,
        ImportDataManager $importDataManager,
        ImportDataFileUploaded $message,
        ImportData $entity
    )
    {
        $message->getId()->willReturn(1);
        $repository->find(1)->willReturn($entity);
        $entity->isImported()->willReturn(false);
        $importDataManager->import($entity)->shouldBeCalled()->willReturn([
            'pe',
            'pi',
            'to'
        ]);
        $this->__invoke($message);
    }

    function it_doesnt_process_message_if_entity_not_found(
        ImportDataRepository $repository,
        ImportDataManager $importDataManager,
        ImportDataFileUploaded $message,
        ImportData $entity
    )
    {
        $message->getId()->willReturn(1);
        $repository->find(1)->willReturn();
        $entity->isImported()->willReturn(false);
        $importDataManager->import(Argument::any())->shouldNotBeCalled();
        $this->__invoke($message);
    }

    function it_doesnt_process_message_if_already_imported(
        ImportDataRepository $repository,
        ImportDataManager $importDataManager,
        ImportDataFileUploaded $message,
        ImportData $entity
    ) {
        $message->getId()->willReturn(1);
        $repository->find(1)->willReturn();
        $entity->isImported()->willReturn(true);
        $importDataManager->import(Argument::any())->shouldNotBeCalled();
        $this->__invoke($message);
    }
}
