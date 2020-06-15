<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2016A;

use App\Exception\AccessDeniedException;
use App\Model\ProductInterface;
use App\Model\Rate;
use App\Model\RatePlanCode;
use App\Security\Voter\PartnerVoter;
use App\Service\ChannelManager\SoapOta\SoapOtaOperationInterface;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class HotelRatePlanOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class HotelRatePlanOperation implements SoapOtaOperationInterface
{
    const OPERATION_NAME = 'OTA_HotelRatePlanRQ';

    /**
     *
     * @var PartnerLoader
     */
    private $partnerLoader;

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
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * HotelRatePlanOperation constructor.
     *
     * @param PartnerLoader                 $partnerLoader
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CmhubLogger                   $logger
     * @param ProductLoader                 $productLoader
     */
    public function __construct(PartnerLoader $partnerLoader, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger, ProductLoader $productLoader)
    {
        $this->partnerLoader = $partnerLoader;
        $this->authorizationChecker = $authorizationChecker;
        $this->logger = $logger;
        $this->productLoader = $productLoader;
    }

    /**
     *
     * @param \StdClass $request
     *
     * @return array
     *
     * @throws AccessDeniedException
     */
    public function handle(\StdClass $request): array
    {
        $partner = $this->partnerLoader->find($hotelCode = $request->RatePlans->RatePlan->HotelRef->HotelCode);
        if (!$partner || !$this->authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)) {
            throw new AccessDeniedException(Response::HTTP_FORBIDDEN, $hotelCode);
        }

        $products = $this->productLoader->getByPartner($partner);

        $this->logger->addOperationInfo(LogAction::GET_RATE_PLANS, $partner, $this);

        return [
            'RatePlans' => [
                'RatePlan' => [
                    [
                        'RatePlanCode'     => RatePlanCode::SBX,
                        'Description'      => [
                            'Name' => 'Name',
                            'Text' => Rate::SBX_RATE_PLAN_NAME,
                        ],
                        'SellableProducts' => [
                            'SellableProduct' => array_map(
                                function (ProductInterface $product) {
                                    return ['InvTypeCode' => $product->getIdentifier()];
                                },
                                $products->toArray()
                            ),
                        ],
                    ],
                ],
            ],
        ];
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
