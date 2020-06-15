<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2010A;

use App\Entity\ChannelManager;
use App\Exception\AccessDeniedException;
use App\Model\PartnerInterface;
use App\Model\ProductRateCollectionInterface;
use App\Model\RatePlanCode;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Operation\V2010A\HotelRateAmountNotifOperation;
use App\Service\ChannelManager\SoapOta\Serializer\V2010A\ProductRateCollectionNormalizer;
use App\Service\Loader\PartnerLoader;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HotelRateAmountNotifOperationSpec extends ObjectBehavior
{
    private $rateAmountMessages;

    private $testData;

    function let(
        BookingEngineInterface $bookingEngine,
        PartnerLoader $partnerLoader,
        AuthorizationCheckerInterface $authorizationChecker,
        CmhubLogger $logger,
        ProductRateCollectionNormalizer $productRateCollectionNormalizer
    )
    {
        $this->beConstructedWith($bookingEngine, $partnerLoader, $authorizationChecker, $logger, $productRateCollectionNormalizer);
        $this->rateAmountMessages = [
            'StatusApplicationControl' => [
                'RatePlanCode' => RatePlanCode::SBX,
                'InvTypeCode' => 'ROOMID1',
                'Start' => '2018-09-01',
                'End' => '2018-09-11',
            ],
            'Rates' => [
                'Rate' => [
                    'BaseByGuestAmts' => [
                        'BaseByGuestAmt' => [
                            'AmountAfterTax' => '2'
                        ]
                    ],
                    'CurrencyCode' => 'EUR'
                ]
            ],
        ];

        $this->testData = [
            'RateAmountMessages' => [
                'RateAmountMessage' => $this->rateAmountMessages,
                'HotelCode' => 'hotel_id'
            ]
        ];
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(HotelRateAmountNotifOperation::class);
    }

    function it_throws_access_denied_exception_if_partner_not_granted(PartnerInterface $partner, AuthorizationCheckerInterface $authorizationChecker, PartnerLoader $partnerLoader)
    {
        $partnerLoader->find('hotel_id')->willReturn($partner);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(false);
        $this->shouldThrow(AccessDeniedException::class)->during('handle', [json_decode(json_encode($this->testData))]);
    }

    function it_updates_rate(
        PartnerInterface $partner,
        AuthorizationCheckerInterface $authorizationChecker,
        PartnerLoader $partnerLoader,
        ProductRateCollectionNormalizer $productRateCollectionNormalizer,
        ProductRateCollectionInterface $productRateCollection,
        BookingEngineInterface $bookingEngine,
        ChannelManager $channelManager
    )
    {
        $partnerLoader->find('hotel_id')->willReturn($partner);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);

        $productRateCollectionNormalizer->denormalize(json_decode(json_encode([$this->rateAmountMessages])), ['partner' => $partner])->willReturn($productRateCollection);
        $bookingEngine->updateRates($productRateCollection)->willReturn($productRateCollection);

        $partner->getIdentifier()->willReturn('hotel_id');
        $partner->getName()->willReturn('Hotel Test');
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('Test CM');

        $this->handle(json_decode(json_encode($this->testData)))->shouldBeLike([]);
    }

    function it_supports()
    {
        $this->supports(HotelRateAmountNotifOperation::OPERATION_NAME)->shouldBe(true);
    }
}
