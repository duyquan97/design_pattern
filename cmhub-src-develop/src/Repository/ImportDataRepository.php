<?php

namespace App\Repository;

use App\Entity\ImportData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class ImportDataRepository
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @codeCoverageIgnore
 */
class ImportDataRepository extends ServiceEntityRepository
{
    /**
     * ImportDataRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportData::class);
    }
}
