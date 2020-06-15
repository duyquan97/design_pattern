<?php

namespace App\MessageHandler\EventListener;

use App\Utils\Monolog\CmhubLogger;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

/**
 * Class WorkerRunningListener
 *
 * Listening event dispatched after the worker processed a message or didn't receive a message at all.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WorkerRunningListener
{
    /**
     * @var CmhubLogger
     */
    private $cmhubLogger;

    /**
     * WorkerRunningListener constructor.
     *
     * @param CmhubLogger $cmhubLogger
     */
    public function __construct(CmhubLogger $cmhubLogger)
    {
        $this->cmhubLogger = $cmhubLogger;
    }

    /**
     *
     * @param WorkerRunningEvent $event
     *
     * @return void
     */
    public function __invoke(WorkerRunningEvent $event)
    {
        // Can't log here as it will generate massive amount of logs
    }
}
