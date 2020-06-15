<?php

namespace App\Repository;

use App\Entity\Experience;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class ExperienceRepository
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @codeCoverageIgnore
 */
class ExperienceRepository extends ServiceEntityRepository
{
    /**
     * ExperienceRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Experience::class);
    }
}
