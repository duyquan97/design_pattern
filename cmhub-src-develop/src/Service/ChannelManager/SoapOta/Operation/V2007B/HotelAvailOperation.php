<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Exception\AccessDeniedException;
use App\Exception\NormalizerNotFoundException;
use App\Security\Voter\PartnerVoter;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\ChannelManager\SoapOta\SoapOtaOperationInterface;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class HotelAvailOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HotelAvailOperation implements SoapOtaOperationInterface
{
    public const OPERATION_NAME = 'OTA_HotelAvailRQ';

    /**
     *
     * @var PartnerLoader
     */
    private $partnerLoader;

    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     *
     * @var SoapSerializer
     */
    private $soapSerializer;

    /**
     *
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * HotelAvailOperation constructor.
     *
     * @param PartnerLoader                 $partnerLoader
     * @param ProductLoader                 $productLoader
     * @param SoapSerializer                $soapSerializer
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CmhubLogger                   $logger
     */
    public function __construct(PartnerLoader $partnerLoader, ProductLoader $productLoader, SoapSerializer $soapSerializer, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger)
    {
        $this->partnerLoader = $partnerLoader;
        $this->productLoader = $productLoader;
        $this->soapSerializer = $soapSerializer;
        $this->authorizationChecker = $authorizationChecker;
        $this->logger = $logger;
    }

    /**
     *
     * @param \StdClass $request
     *
     * @return array
     *
     * @throws AccessDeniedException
     * @throws NormalizerNotFoundException
     */
    public function handle(\StdClass $request): array
    {
        $partner = $this->partnerLoader->find($hotelCode = $request->AvailRequestSegments->AvailRequestSegment->HotelSearchCriteria->Criterion->HotelRef->HotelCode);
        if (!$partner || !$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $products = $this->productLoader->getByPartner($partner);

        $this->logger->addOperationInfo(LogAction::GET_PRODUCTS, $partner, $this);

        return $this->soapSerializer->normalize($products);
    }

    /**
     *
     * @param string $operation The operation
     *
     * @return boolean
     */
    public function supports(string $operation): bool
    {
        return static::OPERATION_NAME === $operation;
    }
}
