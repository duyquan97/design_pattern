<?php

namespace App\Service\HealthChecks;

use App\Entity\Partner;
use App\Exception\IresaClientException;
use App\Service\Iresa\IresaApi;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\FailureInterface;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\SuccessInterface;

/**
 * Class IresaCheck
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaCheck implements CheckInterface
{
    /**
     * @var IresaApi
     */
    private $iresaApi;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     *
     * iresaCheck constructor.
     *
     * @param IresaApi               $iresaApi
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(IresaApi $iresaApi, EntityManagerInterface $entityManager)
    {
        $this->iresaApi = $iresaApi;
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @return SuccessInterface|FailureInterface
     */
    public function check()
    {
        try {
            $partner = $this->entityManager->getRepository(Partner::class)->findOneBy([]);

            if ($partner) {
                try {
                    $startDate = (new \DateTime());
                    $endDate = (new \DateTime())
                        ->setTimestamp(
                            mt_rand(
                                $startDate->getTimestamp(),
                                (new \DateTime())->add(new \DateInterval('P5Y'))->getTimestamp()
                            )
                        );

                    $this->iresaApi->getBookings($partner, $startDate, $endDate);

                    return new Success();
                } catch (IresaClientException $exception) {
                    return new Failure($exception->getResponse());
                } catch (GuzzleException $guzzleException) {
                    return new Failure($guzzleException->getMessage());
                } catch (\Exception $exception) {
                    return new Failure($exception->getMessage());
                }
            }

            return new Failure('Partner not found');
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
        return 'Iresa Check';
    }
}
