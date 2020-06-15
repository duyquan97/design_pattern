<?php

namespace App\Repository;

use App\Entity\Guest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class GuestRepository
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @codeCoverageIgnore
 */
class GuestRepository extends ServiceEntityRepository
{
    /**
     * GuestRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Guest::class);
    }
}
