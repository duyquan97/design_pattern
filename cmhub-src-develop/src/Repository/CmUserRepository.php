<?php

namespace App\Repository;

use App\Entity\CmUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class CmUserRepository
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @codeCoverageIgnore
 */
class CmUserRepository extends ServiceEntityRepository
{
    /**
     * ExperienceRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CmUser::class);
    }
}
