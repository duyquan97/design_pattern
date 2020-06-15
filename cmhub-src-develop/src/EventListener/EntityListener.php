<?php

namespace App\EventListener;

use App\Entity\Experience;
use App\Entity\Partner;
use App\Entity\Product;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * Class EntityListener
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EntityListener
{
    /**
     *
     * @var CmhubLogger
     */
    private $logger;

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
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entities = [
            Partner::class,
            Product::class,
            Experience::class,
        ];

        $entityManager = $eventArgs->getEntityManager();
        $uow = $entityManager->getUnitOfWork();

        //Insertions
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!in_array(get_class($entity), $entities)) {
                continue;
            }

            $this->logger->addRecord(
                \Monolog\Logger::INFO,
                'New entity',
                array_merge(
                    $entity->toArray(),
                    [
                        LogKey::ACTION_KEY => LogAction::DB_INSERT,
                        LogKey::TYPE_KEY => LogType::DB_TYPE,
                        LogKey::ENTITY_KEY => get_class($entity),
                    ]
                ),
                $this
            );
        }
        //Updates
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!in_array(get_class($entity), $entities)) {
                continue;
            }

            $this->logger->addRecord(
                \Monolog\Logger::INFO,
                'Updated entity',
                array_merge(
                    $entity->toArray(),
                    [
                        LogKey::ACTION_KEY => LogAction::DB_UPDATE,
                        LogKey::TYPE_KEY => LogType::DB_TYPE,
                        LogKey::ENTITY_KEY => get_class($entity),
                    ]
                ),
                $this
            );
        }
        //Deletions
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (!in_array(get_class($entity), $entities)) {
                continue;
            }

            $this->logger->addRecord(
                \Monolog\Logger::INFO,
                'Deleted entity',
                array_merge(
                    $entity->toArray(),
                    [
                        LogKey::ACTION_KEY => LogAction::DB_DELETE,
                        LogKey::TYPE_KEY => LogType::DB_TYPE,
                        LogKey::ENTITY_KEY => get_class($entity),
                    ]
                ),
                $this
            );
        }
    }
}
