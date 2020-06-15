<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Entity\ChannelManager;
use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Model\ProductRateCollection;
use App\Model\RatePlanCode;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Operation\V2007B\HotelRateAmountNotifOperation;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HotelRateAmountNotifOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(HotelRateAmountNotifOperation::class);
    }

    function let(
        ProductLoader $productLoader,
        BookingEngineInterface $bookingEngine,
        PartnerInterface $partner,
        ProductInterface $product,
        PartnerLoader $partnerLoader,
        AuthorizationCheckerInterface $authorizationChecker,
        CmhubLogger $logger,
        ChannelManager $channelManager
    ) {
        $this->beConstructedWith($bookingEngine, $partnerLoader, $productLoader, $authorizationChecker, $logger);

        $partnerLoader->find('hotel_id')->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('cm_identifier');
        $partner->getIdentifier()->willReturn('partner_id');
        $partner->getName()->willReturn('partner_name');

        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);
        $productLoader->find($partner, 'ROOMID1')->willReturn($product);
    }

    function it_throws_access_denied_exception_if_partner_not_granted(PartnerInterface $partner, AuthorizationCheckerInterface $authorizationChecker)
    {
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(false);
        $this->shouldThrow(AccessDeniedException::class)->during('handle', [json_decode(json_encode($this->testData))]);
    }

    function it_does_not_throw_exception_if_product_not_found(
        PartnerInterface $partner,
        ProductLoader $productLoader
    )
    {
        $productLoader->find($partner, 'ROOMID1')->willReturn();
        $this->shouldThrow(ProductNotFoundException::class)->during('handle', [json_decode(json_encode($this->testData))]);
    }

    function it_throws_exception_if_wrong_date_format()
    {
        $data = json_decode(json_encode($this->testData));
        $data->RateAmountMessages->RateAmountMessage->Rates->Rate->Start = '20-10-2018';
        $this->shouldThrow(DateFormatException::class)->during('handle', [$data]);
    }

    function it_throws_exception_if_invalid_status_control_format()
    {
        $data = json_decode(json_encode($this->invalidStatusControl));
        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }

    function it_throws_exception_if_missing_start_field()
    {
        $data = json_decode(json_encode($this->missingStart));
        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }

    function it_throws_exception_if_start_date_is_greater_than_end_date()
    {
        $data = json_decode(json_encode($this->testData));
        $data->RateAmountMessages->RateAmountMessage->Rates->Rate->Start = '2018-01-20';
        $data->RateAmountMessages->RateAmountMessage->Rates->Rate->End = '2018-01-10';
        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }

    function it_throws_exception_if_amount_is_under_zero()
    {
        $data = json_decode(json_encode($this->testData));
        $data->RateAmountMessages->RateAmountMessage->Rates->Rate->BaseByGuestAmts->BaseByGuestAmt->AmountAfterTax = '-99';
        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }

    function it_handles_operation(PartnerInterface $partner, BookingEngineInterface $bookingEngine, CmhubLogger $logger)
    {
        $bookingEngine->updateRates(Argument::type(ProductRateCollection::class))->shouldBeCalled();
        $logger->addOperationInfo(LogAction::UPDATE_RATES, $partner, $this)->shouldBeCalled();
        $this->handle(json_decode(json_encode($this->testData)));

        //TODO: Improve test adding factory methods to mock ProductRateCollection being created
    }

    protected $testData = [
        'RateAmountMessages' => [
            'RateAmountMessage' => [
                'StatusApplicationControl' => [
                    'RatePlanCode' => RatePlanCode::SBX,
                    'InvTypeCode'  => 'ROOMID1',
                    'IsRoom'       => true
                ],
                'Rates'                    => [
                    'Rate' => [
                        'BaseByGuestAmts' => [
                            'BaseByGuestAmt' => [
                                'AmountAfterTax' => '2'
                            ]
                        ],
                        'Start'           => '2018-09-01',
                        'End'             => '2018-09-11',
                        'CurrencyCode'    => 'EUR'
                    ]
                ],
            ],
            'HotelCode'         => 'hotel_id'
        ]
    ];

    protected $invalidStatusControl = [
        'RateAmountMessages' => [
            'RateAmountMessage' => [
                'StatusApplicationControl' => [[
                    'RatePlanCode' => RatePlanCode::SBX,
                    'InvTypeCode'  => 'ROOMID1',
                    'IsRoom'       => true
                ]],
                'Rates'                    => [
                    'Rate' => [
                        'BaseByGuestAmts' => [
                            'BaseByGuestAmt' => [
                                'AmountAfterTax' => '2'
                            ]
                        ],
                        'Start'           => '2018-09-01',
                        'End'             => '2018-09-11',
                        'CurrencyCode'    => 'EUR'
                    ]
                ],
            ],
            'HotelCode'         => 'hotel_id'
        ]
    ];

    protected $missingStart = [
        'RateAmountMessages' => [
            'RateAmountMessage' => [
                'StatusApplicationControl' => [
                    'RatePlanCode' => RatePlanCode::SBX,
                    'InvTypeCode'  => 'ROOMID1',
                    'IsRoom'       => true
                ],
                'Rates'                    => [
                    'Rate' => [
                        'BaseByGuestAmts' => [
                            'BaseByGuestAmt' => [
                                'AmountAfterTax' => '2'
                            ]
                        ],
                        'End'             => '2018-09-11',
                        'CurrencyCode'    => 'EUR'
                    ]
                ],
            ],
            'HotelCode'         => 'hotel_id'
        ]
    ];
}
