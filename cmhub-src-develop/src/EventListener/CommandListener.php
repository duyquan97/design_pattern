<?php

namespace App\EventListener;

use Smartbox\CorrelationIdBundle\Service\GenerateAndStorageCorrelationId;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Class CommandListener
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class CommandListener
{
    /**
     * @var GenerateAndStorageCorrelationId
     */
    private $correlationIdService;

    /**
     * CommandListener constructor.
     *
     * @param GenerateAndStorageCorrelationId $correlationIdService
     */
    public function __construct(GenerateAndStorageCorrelationId $correlationIdService)
    {
        $this->correlationIdService = $correlationIdService;
    }


    /**
     * @param ConsoleCommandEvent $event
     *
     * @return void
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $this->correlationIdService->generateAndStorage();
    }
}
