<?php

namespace App\Repository;

use App\Entity\ChannelManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class ChannelManagerRepository
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ChannelManagerRepository extends ServiceEntityRepository
{
    /**
     * ExperienceRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChannelManager::class);
    }
}
