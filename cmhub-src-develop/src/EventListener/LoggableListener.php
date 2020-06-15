<?php

namespace App\EventListener;

use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Monolog\LoggableInterface;
use App\Utils\Monolog\LogKey;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Monolog\Logger;

/**
 * Class LoggableListener
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class LoggableListener
{
    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * @var array
     */
    private $inserted;

    /**
     * @var array
     */
    private $updated;

    /**
     * @var array
     */
    private $removed;

    /**
     * EntityListener constructor.
     *
     * @param CmhubLogger $logger
     */
    public function __construct(CmhubLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Gets all the entities to flush
     *
     * @param OnFlushEventArgs $eventArgs Event args
     *
     * @return void
     *
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->removed = [];
        $this->updated = [];
        $this->inserted = [];

        $entityManager = $eventArgs->getEntityManager();
        $uow = $entityManager->getUnitOfWork();

        //Insertions
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof LoggableInterface) {
                continue;
            }

            $this->inserted[] = $entity;
        }

        //Updates
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof LoggableInterface) {
                continue;
            }

            $this->updated[] = $entity;
        }

        //Deletions
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!$entity instanceof LoggableInterface) {
                continue;
            }

            $this->removed[] = $entity;
        }
    }

    /**
     *
     * @param PostFlushEventArgs $eventArgs
     *
     * @return void
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        foreach ($this->inserted as $entity) {
            $this->logger->addRecord(
                Logger::INFO,
                'New entity',
                array_merge(
                    $entity->toArray(),
                    [
                        LogKey::ACTION_KEY => LogAction::LOG_CREATE,
                    ]
                ),
                $this
            );
        }

        foreach ($this->updated as $entity) {
            $this->logger->addRecord(
                Logger::INFO,
                'Updated entity',
                array_merge(
                    $entity->toArray(),
                    [
                        LogKey::ACTION_KEY => LogAction::LOG_UPDATE,
                    ]
                ),
                $this
            );
        }

        foreach ($this->removed as $entity) {
            $this->logger->addRecord(
                Logger::INFO,
                'Deleted entity',
                array_merge(
                    $entity->toArray(),
                    [
                        LogKey::ACTION_KEY => LogAction::LOG_DELETE,
                    ]
                ),
                $this
            );
        }
    }
}
