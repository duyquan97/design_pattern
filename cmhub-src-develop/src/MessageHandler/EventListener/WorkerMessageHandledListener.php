<?php

namespace App\MessageHandler\EventListener;

use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

/**
 * Class WorkerMessageHandledListener
 *
 * Listening event dispatched after a message was received from a transport and successfully handled.
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WorkerMessageHandledListener
{
    /**
     * @var CmhubLogger
     */
    private $cmhubLogger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * WorkerMessageHandledListener constructor.
     *
     * @param CmhubLogger            $cmhubLogger
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(CmhubLogger $cmhubLogger, EntityManagerInterface $entityManager)
    {
        $this->cmhubLogger = $cmhubLogger;
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @param WorkerMessageHandledEvent $event
     *
     * @return void
     */
    public function __invoke(WorkerMessageHandledEvent $event)
    {
        $this
            ->cmhubLogger
            ->addRecord(
                Logger::INFO,
                'Message handled by worker',
                [
                    LogKey::TYPE_KEY                => LogType::MESSENGER,
                    LogKey::MESSENGER_EVENT         => 'WorkerMessageHandled',
                    LogKey::MESSENGER_MESSAGE_TYPE  => get_class($event->getEnvelope()->getMessage()),
                    LogKey::MESSENGER_RECEIVER_NAME => $event->getReceiverName(),
                    LogKey::MY_PID                  => getmypid() ?: '',
                ]
            );

        $this->entityManager->clear();
    }
}
