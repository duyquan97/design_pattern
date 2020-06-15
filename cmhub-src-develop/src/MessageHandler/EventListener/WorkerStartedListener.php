<?php

namespace App\MessageHandler\EventListener;

use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Monolog\Logger;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;

/**
 * Class WorkerStartedListener
 *
 * Listening event dispatched when a worker has been started.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WorkerStartedListener
{
    /**
     * @var CmhubLogger
     */
    private $cmhubLogger;

    /**
     * WorkerStartedListener constructor.
     *
     * @param CmhubLogger $cmhubLogger
     */
    public function __construct(CmhubLogger $cmhubLogger)
    {
        $this->cmhubLogger = $cmhubLogger;
    }

    /**
     *
     * @param WorkerStartedEvent $event
     *
     * @return void
     */
    public function __invoke(WorkerStartedEvent $event)
    {

        $this
            ->cmhubLogger
            ->addRecord(
                Logger::INFO,
                'Worker started',
                [
                    LogKey::TYPE_KEY        => LogType::MESSENGER,
                    LogKey::MESSENGER_EVENT => 'WorkerStarted',
                    LogKey::MY_PID          => getmypid() ?: '',
                ]
            );
    }
}
