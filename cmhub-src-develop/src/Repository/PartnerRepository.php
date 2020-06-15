<?php

namespace App\Repository;

use App\Entity\Partner;
use App\Entity\PartnerStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class PartnerRepository
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @codeCoverageIgnore
 */
class PartnerRepository extends ServiceEntityRepository
{
    /**
     * PartnerRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partner::class);
    }

    /**
     *
     * @param array $ids
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return IterableResult
     */
    public function iterate(array $ids, int $limit = null, int $offset = null)
    {
        $queryBuilder = $this
            ->createQueryBuilder('partner')
            ->where('partner.enabled = :enabled')
            ->andWhere('partner.status = :status')
            ->setParameter('enabled', true)
            ->setParameter('status', PartnerStatus::PARTNER);

        if ($ids) {
            $queryBuilder
                ->andWhere('partner.identifier IN (:ids)')
                ->setParameter('ids', $ids);
        }

        if (is_int($limit) && is_int($offset)) {
            $queryBuilder->setMaxResults($limit)->setFirstResult($offset);
        }


        return $queryBuilder
            ->getQuery()
            ->iterate();
    }

    /**
     *
     * @param array $ids
     *
     * @return int|mixed
     */
    public function countByIdentifiers(array $ids)
    {
        try {
            $queryBuilder = $this
                ->createQueryBuilder('partner')
                ->select('COUNT(partner.id)')
                ->where('partner.enabled = :enabled')
                ->andWhere('partner.status = :status')
                ->setParameter('enabled', true)
                ->setParameter('status', PartnerStatus::PARTNER);

            if ($ids) {
                $queryBuilder
                    ->andWhere('partner.identifier IN (:ids)')
                    ->setParameter('ids', $ids);
            }

            return $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $exception) {
            return 0;
        }
    }
}
