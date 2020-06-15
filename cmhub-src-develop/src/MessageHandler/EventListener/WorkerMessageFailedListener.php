<?php

namespace App\MessageHandler\EventListener;

use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Monolog\Logger;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

/**
 * Class WorkerMessageFailedListener
 *
 * Dispatched when a message was received from a transport and handling failed.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WorkerMessageFailedListener
{
    /**
     * @var CmhubLogger
     */
    private $cmhubLogger;

    /**
     * WorkerMessageFailedListener constructor.
     *
     * @param CmhubLogger $cmhubLogger
     */
    public function __construct(CmhubLogger $cmhubLogger)
    {
        $this->cmhubLogger = $cmhubLogger;
    }

    /**
     *
     * @param WorkerMessageFailedEvent $event
     *
     * @return void
     */
    public function __invoke(WorkerMessageFailedEvent $event)
    {
        $this
            ->cmhubLogger
            ->addRecord(
                Logger::INFO,
                'Worker failed to process message',
                [
                    LogKey::TYPE_KEY                => LogType::MESSENGER,
                    LogKey::MESSENGER_EVENT         => 'WorkerMessageFailed',
                    LogKey::MESSENGER_MESSAGE_TYPE  => get_class($event->getEnvelope()->getMessage()),
                    LogKey::MESSAGE_KEY             => $event->getThrowable()->getMessage(),
                    LogKey::MESSENGER_RECEIVER_NAME => $event->getReceiverName(),
                    LogKey::MY_PID                  => getmypid() ?: '',
                ]
            );
    }
}
