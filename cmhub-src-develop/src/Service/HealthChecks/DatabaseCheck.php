<?php

namespace App\Service\HealthChecks;

use App\Entity\Partner;
use Doctrine\ORM\EntityManagerInterface;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\FailureInterface;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\SuccessInterface;

/**
 * Class DatabaseCheck
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class DatabaseCheck implements CheckInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     *
     * databaseCheck constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @return SuccessInterface|FailureInterface
     */
    public function check()
    {
        try {
            /** @var Partner $partner */
            $partner = $this->entityManager->getRepository(Partner::class)->findOneBy([]);
            if ($partner) {
                return new Success('Database is up and running');
            }

            return new Failure('The partner returned from DB is null');
        } catch (\Exception $exception) {
            return new Failure($exception->getMessage());
        }
    }

    /**
     *
     * @return string
     */
    public function getLabel()
    {
        return 'Database Check';
    }
}
