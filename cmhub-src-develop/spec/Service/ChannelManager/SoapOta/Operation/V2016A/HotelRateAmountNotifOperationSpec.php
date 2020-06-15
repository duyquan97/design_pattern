<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2016A;

use App\Entity\ChannelManager;
use App\Exception\AccessDeniedException;
use App\Model\PartnerInterface;
use App\Model\ProductRateCollection;
use App\Model\ProductRateCollectionInterface;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Operation\V2016A\HotelRateAmountNotifOperation;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\Loader\PartnerLoader;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HotelRateAmountNotifOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(HotelRateAmountNotifOperation::class);
    }

    function let(BookingEngineInterface $bookingEngine, PartnerLoader $partnerLoader, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger, SoapSerializer $soapSerializer)
    {
        $this->beConstructedWith($bookingEngine, $partnerLoader, $authorizationChecker, $logger, $soapSerializer);
    }

    function it_supports()
    {
        $this->supports(HotelRateAmountNotifOperation::OPERATION_NAME)->shouldBe(true);
    }

    function it_handles(
        PartnerLoader $partnerLoader,
        PartnerInterface $partner,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRateCollectionInterface $productRateCollection,
        SoapSerializer $soapSerializer,
        BookingEngineInterface $bookingEngine,
        ChannelManager $channelManager
    )
    {

        $request2 = [
            "StatusApplicationControl" =>
                [
                    "InvTypeCode" => "358999",
                    "Start" => "2019-02-21",
                    "End" => "2019-02-28",
                    "RatePlanCode" => "1",
                ],
            "Rates" => [
                "Rate" => [
                    "BaseByGuestAmts" => [
                        "BaseByGuestAmt" => [
                            [
                                "AmountAfterTax" => "1",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $request2Class = json_decode(json_encode($request2));

        $request = [
            "RateAmountMessages" => [
                "RateAmountMessage" => $request2,
                "HotelCode" => '00145999',
            ],
        ];

        $requestClass = json_decode(json_encode($request));

        $partnerLoader->find('00145999')->willReturn($partner);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);

        $soapSerializer->denormalize([$request2Class], ProductRateCollection::class, ['partner' => $partner])->willReturn($productRateCollection);

        $bookingEngine->updateRates($productRateCollection)->willReturn($productRateCollection);

        $partner->getIdentifier()->willReturn('358999');
        $partner->getName()->willReturn('standard');
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('smarthotel');

        $this->handle($requestClass)->shouldBeLike([]);
    }

    function it_denieds_access(
        PartnerLoader $partnerLoader,
        PartnerInterface $partner
    )
    {

        $request2 = [
            "StatusApplicationControl" =>
                [
                    "InvTypeCode" => "358999",
                    "Start" => "2019-02-21",
                    "End" => "2019-02-28",
                    "RatePlanCode" => "1",
                ],
            "Rates" => [
                "Rate" => [
                    "BaseByGuestAmts" => [
                        "BaseByGuestAmt" => [
                            [
                                "AmountAfterTax" => "1",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $request = [
            "RateAmountMessages" => [
                "RateAmountMessage" => $request2,
                "HotelCode" => '00145999',
            ],
        ];

        $requestClass = json_decode(json_encode($request));

        $partnerLoader->find('00145999')->willReturn($partner);

        $this->shouldThrow(AccessDeniedException::class)->during('handle', [$requestClass]);
    }
}
