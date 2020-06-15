<?php

namespace App\MessageHandler\EventListener;

use App\Message\CorrelatedIdInterface;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Monolog\Logger;
use Smartbox\CorrelationIdBundle\StorageEngine\StorageEngineInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/**
 * Class MessageReceivedListener
 *
 * Listening event dispatched when a message was received from a transport but before sent to the bus.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WorkerMessageReceivedListener
{
    /**
     * @var StorageEngineInterface
     */
    private $storageEngine;

    /**
     * @var CmhubLogger
     */
    private $cmhubLogger;

    /**
     * WorkerMessageReceivedListener constructor.
     *
     * @param StorageEngineInterface $storageEngine
     * @param CmhubLogger            $cmhubLogger
     */
    public function __construct(StorageEngineInterface $storageEngine, CmhubLogger $cmhubLogger)
    {
        $this->storageEngine = $storageEngine;
        $this->cmhubLogger = $cmhubLogger;
    }

    /**
     * Sets the correlation id.
     *
     * @param WorkerMessageReceivedEvent $event
     *
     * @return void
     */
    public function __invoke(WorkerMessageReceivedEvent $event)
    {
        $message = $event->getEnvelope()->getMessage();
        if ($message instanceof CorrelatedIdInterface) {
            $this->storageEngine->setCorrelationId($message->getCorrelationId());
        }

        $this
            ->cmhubLogger
            ->addRecord(
                Logger::INFO,
                'Worker message received',
                [
                    LogKey::TYPE_KEY                => LogType::MESSENGER,
                    LogKey::MESSENGER_EVENT         => 'WorkerMessageReceived',
                    LogKey::MESSENGER_MESSAGE_TYPE  => get_class($event->getEnvelope()->getMessage()),
                    LogKey::MESSENGER_RECEIVER_NAME => $event->getReceiverName(),
                    LogKey::MY_PID                  => getmypid() ?: '',
                ]
            );
    }
}
