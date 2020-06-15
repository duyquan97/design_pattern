<?php

namespace App\MessageHandler\EventListener;

use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Monolog\Logger;
use Symfony\Component\Messenger\Event\WorkerStoppedEvent;

/**
 * Class WorkerStoppedListener
 *
 * Listening event dispatched after the worker processed a message or didn't receive a message at all.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WorkerStoppedListener
{
    /**
     * @var CmhubLogger
     */
    private $cmhubLogger;

    /**
     * WorkerStoppedListener constructor.
     *
     * @param CmhubLogger $cmhubLogger
     */
    public function __construct(CmhubLogger $cmhubLogger)
    {
        $this->cmhubLogger = $cmhubLogger;
    }

    /**
     *
     * @param WorkerStoppedEvent $event
     *
     * @return void
     */
    public function __invoke(WorkerStoppedEvent $event)
    {
        $this
            ->cmhubLogger
            ->addRecord(
                Logger::INFO,
                'Worker stopped',
                [
                    LogKey::TYPE_KEY        => LogType::MESSENGER,
                    LogKey::MESSENGER_EVENT => 'WorkerStopped',
                    LogKey::MY_PID          => getmypid() ?: '',
                ]
            );
    }
}
