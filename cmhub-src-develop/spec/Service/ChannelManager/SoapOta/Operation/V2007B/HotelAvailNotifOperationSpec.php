<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Entity\ChannelManager;
use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductInterface;
use App\Model\RatePlanCode;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Operation\V2007B\HotelAvailNotifOperation;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class HotelAvailNotifOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(HotelAvailNotifOperation::class);
    }

    function let(
        CmhubLogger $logger,
        BookingEngineInterface $bookingEngine,
        PartnerLoader $partnerLoader,
        ProductLoader $productLoader,
        AuthorizationCheckerInterface $authorizationChecker,
        PartnerInterface $partner,
        ProductInterface $product,
        ProductInterface $product1,
        ChannelManager $channelManager
    )
    {
        $this->beConstructedWith($bookingEngine, $productLoader, $partnerLoader, $authorizationChecker, $logger);

        $partnerLoader->find('partner_id')->willReturn($partner);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);
        $productLoader->find($partner, 'ROOMID1')->willReturn($product);
        $productLoader->find($partner, 'ROOMID2')->willReturn($product1);
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('cm_identifier');
        $partner->getIdentifier()->willReturn('partner_id');
        $partner->getName()->willReturn('partner_name');
    }

    function it_throws_access_denied_exception_if_partner_is_not_granted(AuthorizationCheckerInterface $authorizationChecker, PartnerInterface $partner)
    {
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->shouldBeCalled()->willReturn(false);
        $this->shouldThrow(AccessDeniedException::class)->during('handle', [json_decode(json_encode($this->testData))]);
    }

    function it_does_not_throw_exception_if_product_not_found(ProductLoader $productLoader, PartnerInterface $partner,BookingEngineInterface $bookingEngine,CmhubLogger $logger)
    {
        $productLoader->find($partner, 'ROOMID1')->willReturn();
        $productLoader->find($partner, 'ROOMID2')->willReturn();
        $bookingEngine->updateAvailability(Argument::type(ProductAvailabilityCollection::class))->shouldBeCalled();
        $logger->addOperationException(Argument::cetera())->shouldBeCalledTimes(3);
        $logger->addOperationInfo(LogAction::UPDATE_AVAILABILITY, $partner, $this)->shouldBeCalled();

        $this->handle(json_decode(json_encode($this->testData)));
    }

    function it_throws_exception_if_wrong_date_format()
    {
        $data = json_decode(json_encode($this->testData));
        $data->AvailStatusMessages->AvailStatusMessage[0]->StatusApplicationControl->Start = '20-01-2018';
        $this->shouldThrow(DateFormatException::class)->during('handle', [$data]);
    }

    function it_throws_exception_if_start_date_is_greater_than_end_date()
    {
        $data = json_decode(json_encode($this->testData));
        $data->AvailStatusMessages->AvailStatusMessage[0]->StatusApplicationControl->Start = '2018-01-20';
        $data->AvailStatusMessages->AvailStatusMessage[0]->StatusApplicationControl->End = '2018-01-10';
        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }

    function it_throws_exception_if_stock_is_less_than_zero()
    {
        $data = json_decode(json_encode($this->testData));
        $data->AvailStatusMessages->AvailStatusMessage[0]->BookingLimit = '-1';
        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }

    function it_throws_exception_if_stock_is_more_than_nine_thousand_nine_hundred_ninety_nine()
    {
        $data = json_decode(json_encode($this->testData));
        $data->AvailStatusMessages->AvailStatusMessage[0]->BookingLimit = '99999';
        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }

    function it_handles_operation(PartnerInterface $partner, BookingEngineInterface $bookingEngine, CmhubLogger $logger)
    {
        $bookingEngine->updateAvailability(Argument::type(ProductAvailabilityCollection::class))->shouldBeCalled();
        $logger->addOperationInfo(LogAction::UPDATE_AVAILABILITY, $partner, $this)->shouldBeCalled();

        $this->handle(json_decode(json_encode($this->testData)))->shouldBe([]);
    }

    protected $testData = [
        'AvailStatusMessages' => [
            'HotelCode'          => 'partner_id',
            'AvailStatusMessage' => [
                [
                    'StatusApplicationControl' => [
                        'Start'        => '2018-01-01',
                        'End'          => '2018-01-11',
                        'RatePlanCode' => RatePlanCode::SBX,
                        'InvTypeCode'  => 'ROOMID1'
                    ],
                    'RestrictionStatus'        => [
                        'Status' => 'Open'
                    ],
                    'BookingLimit'             => 3
                ],
                [
                    'StatusApplicationControl' => [
                        'Start'        => '2018-01-01',
                        'End'          => '2018-01-11',
                        'RatePlanCode' => RatePlanCode::SBX,
                        'InvTypeCode'  => 'ROOMID1'
                    ],
                    'LengthsOfStay'        => [
                        'LengthOfStay' => [
                            'MinMaxMessageType' =>  'SetMinLOS',
                            'Time' => '1'
                        ]
                    ],
                ],
                [
                    'StatusApplicationControl' => [
                        'Start'        => '2018-01-01',
                        'End'          => '2018-01-11',
                        'RatePlanCode' => RatePlanCode::SBX,
                        'InvTypeCode'  => 'ROOMID2'
                    ],
                    'RestrictionStatus'        => [
                        'Status' => 'Open'
                    ],
                    'BookingLimit'             => 3
                ]
            ]
        ]
    ];
}
