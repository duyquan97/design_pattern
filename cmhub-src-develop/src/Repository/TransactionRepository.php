<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class TransactionRepository
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @codeCoverageIgnore
 */
class TransactionRepository extends ServiceEntityRepository
{
    /**
     * TransactionRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @param array       $types
     * @param array       $statuses
     * @param int|null    $limit
     * @param string|null $channel
     *
     * @return IterableResult
     */
    public function findByTypesAndStatusesAndChannel(array $types, array $statuses, int $limit = null, string $channel = null): IterableResult
    {
        $queryBuilder = $this->createQueryBuilder('t')
                   ->where('t.status IN (:statuses)')
                   ->andWhere('t.type IN (:types)')
                   ->setParameter('statuses', $statuses)
                   ->setParameter('types', $types);

        if ($channel) {
            $queryBuilder
                ->andwhere('t.channel = :channel')
                ->setParameter('channel', $channel);
        }

        if (is_int($limit)) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->iterate();
    }

    /**
     * @param array       $types
     * @param array       $statuses
     * @param string|null $channel
     *
     * @return int
     */
    public function countByTypesAndStatusAndChannel(array $types, array $statuses, string $channel = null): int
    {
        try {
            $queryBuilder = $this->createQueryBuilder('t')
                       ->select('count(t.id)')
                       ->where('t.status IN (:statuses)')
                       ->andWhere('t.type IN (:types)')
                       ->setParameter('statuses', $statuses)
                       ->setParameter('types', $types);

            if ($channel) {
                $queryBuilder->andWhere('t.channel = :channel')
                   ->setParameter('channel', $channel);
            }

            return $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $exception) {
            return 0;
        }
    }
}
