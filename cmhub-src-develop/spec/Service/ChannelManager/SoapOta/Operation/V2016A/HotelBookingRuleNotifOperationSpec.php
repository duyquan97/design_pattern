<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2016A;

use App\Entity\Product;
use App\Exception\AccessDeniedException;
use App\Exception\PartnerNotFoundException;
use App\Model\Availability;
use App\Model\PartnerInterface;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductCollection;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Operation\V2016A\HotelBookingRuleNotifOperation;
use App\Service\ChannelManager\SoapOta\Operation\V2016A\HotelDescriptiveInfoOperation;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HotelBookingRuleNotifOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(HotelBookingRuleNotifOperation::class);
    }

    function let(BookingEngineInterface $bookingEngine, PartnerLoader $partnerLoader, ProductLoader $productLoader, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger)
    {
        $this->beConstructedWith($bookingEngine, $partnerLoader, $productLoader, $authorizationChecker, $logger);
    }

    function it_supports_operation()
    {
        $this->supports(HotelBookingRuleNotifOperation::OPERATION_NAME)->shouldBe(true);
    }

    function it_handles_operation(BookingEngineInterface $bookingEngine, PartnerLoader $partnerLoader, ProductLoader $productLoader,
          PartnerInterface $partner, Product $product, AuthorizationCheckerInterface $authorizationChecker)
    {
        $request = [
            "RuleMessages" => [
                "HotelCode" => "1",
                "RuleMessage" => [
                    "StatusApplicationControl" => [
                        "InvTypeCode" => "2",
                        "Start" => "2019-03-15",
                        "End" => "2019-03-17",
                        "Mon" => "true",
                        "Fri" => "true",
                    ],
                    "BookingRules" => [
                        "BookingRule" => [
                            "RestrictionStatus" => [
                                "Restriction" => "Master",
                                "Status" => "Close",
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $partnerLoader->find('1')->willReturn($partner);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);
        $productLoader->find($partner, '2')->willReturn($product);

        $bookingEngine->updateAvailability(Argument::type(ProductAvailabilityCollection::class))->shouldBeCalled();
        $this->handle(json_decode(json_encode($request)))->shouldBeLike([]);
    }

    function it_gets_access_denied(PartnerLoader $partnerLoader, PartnerInterface $partner, AuthorizationCheckerInterface $authorizationChecker)
    {
        $partnerLoader->find('1')->willReturn($partner);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(false);

        $this->shouldThrow(AccessDeniedException::class)->during('handle', [json_decode(json_encode($this->request))]);
    }

    protected $request = [
        "RuleMessages" => [
            "HotelCode" => "1",
            "RuleMessage" => [
                "StatusApplicationControl" => [
                    "InvTypeCode" => "2",
                    "Start" => "2019-03-15",
                    "End" => "2019-03-20",
                    "Mon" => "true",
                    "Fri" => "true",
                ],
                "BookingRules" => [
                    "BookingRule" => [
                        "RestrictionStatus" => [
                            "Restriction" => "Master",
                            "Status" => "Close",
                        ],
                    ],
                ],
            ],
        ],
    ];
}
