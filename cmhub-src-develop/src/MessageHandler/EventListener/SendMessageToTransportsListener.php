<?php

namespace App\MessageHandler\EventListener;

use App\Message\CorrelatedIdInterface;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Monolog\Logger;
use Smartbox\CorrelationIdBundle\StorageEngine\StorageEngineInterface;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;

/**
 * Class SendMessageToTransportListener
 *
 * Listening event dispatched before a message is sent to the transport.
 *
 * The event is *only* dispatched if the message will actually
 * be sent to at least one transport. If the message is sent
 * to multiple transports, the message is dispatched only one time.
 * This message is only dispatched the first time a message
 * is sent to a transport, not also if it is retried.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SendMessageToTransportsListener
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
     * SendMessageToTransportsListener constructor.
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
     *
     * @param SendMessageToTransportsEvent $event
     *
     * @return void
     */
    public function __invoke(SendMessageToTransportsEvent $event)
    {
        $message = $event->getEnvelope()->getMessage();
        if (!$message instanceof CorrelatedIdInterface) {
            throw new \LogicException(sprintf('The message `%s` must implement CorrelatedIdInterface in order to keep the correlation id generated at the time of the request', get_class($message)));
        }

        $message->setCorrelationId($this->storageEngine->getCorrelationId());

        $this
            ->cmhubLogger
            ->addRecord(
                Logger::INFO,
                'Message sent to transport',
                [
                    LogKey::TYPE_KEY               => LogType::MESSENGER,
                    LogKey::MESSENGER_EVENT        => 'SendMessageToTransports',
                    LogKey::MESSENGER_MESSAGE_TYPE => get_class($message),
                    LogKey::MY_PID                 => getmypid() ?: '',
                ]
            );
    }
}
