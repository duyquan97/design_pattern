<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2016A;

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
 * Class HotelDescriptiveInfoOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HotelDescriptiveInfoOperation implements SoapOtaOperationInterface
{
    const OPERATION_NAME = 'OTA_HotelDescriptiveInfoRQ';

    /**
     *
     * @var SoapSerializer
     */
    private $soapSerializer;

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
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * HotelDescriptiveInfoOperation constructor.
     *
     * @param SoapSerializer                $soapSerializer
     * @param PartnerLoader                 $partnerLoader
     * @param ProductLoader                 $productLoader
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CmhubLogger                   $logger
     */
    public function __construct(SoapSerializer $soapSerializer, PartnerLoader $partnerLoader, ProductLoader $productLoader, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger)
    {
        $this->soapSerializer = $soapSerializer;
        $this->partnerLoader = $partnerLoader;
        $this->productLoader = $productLoader;
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
        $partner = $this->partnerLoader->find($hotelCode = $request->HotelDescriptiveInfos->HotelDescriptiveInfo->HotelCode);
        if (!$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        if (!isset($request->HotelDescriptiveInfos->HotelDescriptiveInfo->FacilityInfo)) {
            return [];
        }

        if (!isset($request->HotelDescriptiveInfos->HotelDescriptiveInfo->FacilityInfo->SendGuestRooms)) {
            return [];
        }

        $this->logger->addOperationInfo(LogAction::GET_PRODUCTS, $partner, $this);

        if (!$request->HotelDescriptiveInfos->HotelDescriptiveInfo->FacilityInfo->SendGuestRooms) {
            return [
                'HotelDescriptiveContents' => [
                    'HotelDescriptiveContent' => [
                    ],
                ],
            ];
        }

        $productCollection = $this->productLoader->getByPartner($partner);

        return [
            'HotelDescriptiveContents' => [
                'HotelDescriptiveContent' => [
                    'FacilityInfo' => $this->soapSerializer->normalize($productCollection),
                ],
            ],
        ];
    }

    /**
     *
     * @param string $operation
     *
     * @return bool
     */
    public function supports(string $operation): bool
    {
        return static::OPERATION_NAME === $operation;
    }
}
