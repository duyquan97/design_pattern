<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2015A;

use App\Entity\ChannelManager;
use App\Entity\Product;
use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\ValidationException;
use App\Model\Availability;
use App\Model\Factory\AvailabilityFactory;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductInterface;
use App\Model\RatePlanCode;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Operation\V2010A\HotelRateAmountNotifOperation;
use App\Service\ChannelManager\SoapOta\Operation\V2015A\HotelAvailNotifOperation;
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
        ProductAvailabilityCollectionFactory $availabilityCollectionFactory,
        AvailabilityFactory $availabilityFactory,
        PartnerInterface $partner,
        ProductInterface $product,
        ProductInterface $product1,
        ChannelManager $channelManager
    )
    {
        $this->beConstructedWith($bookingEngine, $productLoader, $partnerLoader, $authorizationChecker, $availabilityCollectionFactory, $availabilityFactory, $logger);

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

    function it_does_not_throw_exception_if_product_not_found(ProductLoader $productLoader, PartnerInterface $partner, BookingEngineInterface $bookingEngine,
        CmhubLogger $logger, ProductAvailabilityCollection $collection, Product $product, AvailabilityFactory $availabilityFactory,
        Availability $availability3, Availability $availability4, ProductAvailabilityCollectionFactory $availabilityCollectionFactory)
    {
        $availabilityCollectionFactory->create($partner)->willReturn($collection);
        $productLoader->find($partner, 'ROOMID1')->willReturn(null);
        $productLoader->find($partner, 'ROOMID2')->willReturn($product);
        $availabilityFactory->create(
            Argument::that(function(\DateTime $date) {
                return '2019-10-01' === $date->format('Y-m-d');
            }),
            Argument::that(function(\DateTime $date) {
                return '2019-10-01' === $date->format('Y-m-d');
            }),
            null,
            $product
        )->willReturn($availability3);
        $availabilityFactory->create(
            Argument::that(function(\DateTime $date) {
                return '2019-10-02' === $date->format('Y-m-d');
            }),
            Argument::that(function(\DateTime $date) {
                return '2019-10-02' === $date->format('Y-m-d');
            }),
            null,
            $product
        )->willReturn($availability4);

        $availability3->setStock(4)->shouldBeCalledOnce();
        $availability3->setStopSale(true)->shouldBeCalledOnce();
        $availability3->getStock()->willReturn(4);
        $availability3->isStopSale()->willReturn(true);

        $availability4->setStock(5)->shouldBeCalledOnce();
        $availability4->setStopSale(Argument::any())->shouldNotBeCalled();
        $availability4->isStopSale()->willReturn(null);
        $availability4->getStock()->willReturn(5);

        $collection->addAvailability($availability3)->shouldBeCalledOnce();
        $collection->addAvailability($availability4)->shouldBeCalledOnce();

        $bookingEngine->updateAvailability($collection)->shouldBeCalled();
        $logger->addOperationException(Argument::cetera())->shouldBeCalledTimes(2);
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

    function it_handles_operation(PartnerInterface $partner, BookingEngineInterface $bookingEngine, PartnerLoader $partnerLoader,
        CmhubLogger $logger, ProductAvailabilityCollectionFactory $availabilityCollectionFactory, ProductAvailabilityCollection $availabilityCollection,
        AvailabilityFactory $availabilityFactory, ProductLoader $productLoader, Product $product1, Product $product2, Availability $availability1,
        Availability $availability2, Availability $availability3, Availability $availability4)
    {

        $partnerLoader->find('partner_id')->willReturn($partner);
        $availabilityCollectionFactory->create($partner)->willReturn($availabilityCollection);
        $productLoader->find($partner, 'ROOMID1')->willReturn($product1);
        $productLoader->find($partner, 'ROOMID2')->willReturn($product2);
        $availabilityFactory->create(
            Argument::that(function(\DateTime $date) {
                return '2019-10-01' === $date->format('Y-m-d');
            }),
            Argument::that(function(\DateTime $date) {
                return '2019-10-01' === $date->format('Y-m-d');
            }),
            null,
            $product1
        )->willReturn($availability1);
        $availabilityFactory->create(
            Argument::that(function(\DateTime $date) {
                return '2019-10-02' === $date->format('Y-m-d');
            }),
            Argument::that(function(\DateTime $date) {
                return '2019-10-02' === $date->format('Y-m-d');
            }),
            null,
            $product1
        )->willReturn($availability2);
        $availabilityFactory->create(
            Argument::that(function(\DateTime $date) {
                return '2019-10-01' === $date->format('Y-m-d');
            }),
            Argument::that(function(\DateTime $date) {
                return '2019-10-01' === $date->format('Y-m-d');
            }),
            null,
            $product2
        )->willReturn($availability3);
        $availabilityFactory->create(
            Argument::that(function(\DateTime $date) {
                return '2019-10-02' === $date->format('Y-m-d');
            }),
            Argument::that(function(\DateTime $date) {
                return '2019-10-02' === $date->format('Y-m-d');
            }),
            null,
            $product2
        )->willReturn($availability4);

        $availability1->setStock(3)->shouldBeCalledOnce();
        $availability1->setStopSale(false)->shouldBeCalledOnce();
        $availability1->getStock()->willReturn(3);
        $availability1->isStopSale()->willReturn(false);

        $availability2->setStock(Argument::any())->shouldNotBeCalled();
        $availability2->setStopSale(Argument::any())->shouldNotBeCalled();
        $availability2->isStopSale()->willReturn(null);
        $availability2->getStock()->willReturn(null);

        $availability3->setStock(4)->shouldBeCalledOnce();
        $availability3->setStopSale(true)->shouldBeCalledOnce();
        $availability3->getStock()->willReturn(4);
        $availability3->isStopSale()->willReturn(true);

        $availability4->setStock(5)->shouldBeCalledOnce();
        $availability4->setStopSale(Argument::any())->shouldNotBeCalled();
        $availability4->getStock()->willReturn(5);
        $availability4->isStopSale()->willReturn(null);

        $availabilityCollection->addAvailability($availability1)->shouldBeCalledOnce();
        $availabilityCollection->addAvailability($availability2)->shouldNotBeCalled();
        $availabilityCollection->addAvailability($availability3)->shouldBeCalledOnce();
        $availabilityCollection->addAvailability($availability4)->shouldBeCalledOnce();

        $bookingEngine->updateAvailability($availabilityCollection)->shouldBeCalled();
        $logger->addOperationInfo(LogAction::UPDATE_AVAILABILITY, $partner, $this)->shouldBeCalled();

        $this->handle(json_decode(json_encode($this->testData)))->shouldBe([]);
    }

    function it_supports()
    {
        $this->supports(HotelAvailNotifOperation::OPERATION_NAME)->shouldBe(true);
    }
    protected $testData = [
        'AvailStatusMessages' => [
            'HotelCode'          => 'partner_id',
            'AvailStatusMessage' => [
                [
                    'StatusApplicationControl' => [
                        'Start'        => '2019-10-01',
                        'End'          => '2019-10-01',
                        'RatePlanCode' => RatePlanCode::SBX,
                        'InvTypeCode'  => 'ROOMID1'
                    ],
                    'RestrictionStatus'        => [
                        'Status' => 'Open',
                        'Restriction' => 'Master'
                    ],
                    'BookingLimit'             => 3
                ],
                [
                    'StatusApplicationControl' => [
                        'Start'        => '2019-10-02',
                        'End'          => '2019-10-02',
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
                        'Start'        => '2019-10-01',
                        'End'          => '2019-10-01',
                        'RatePlanCode' => RatePlanCode::SBX,
                        'InvTypeCode'  => 'ROOMID2'
                    ],
                    'RestrictionStatus'        => [
                        'Status' => 'Close',
                        'Restriction' => 'Master'
                    ],
                    'BookingLimit'             => 4
                ],
                [
                    'StatusApplicationControl' => [
                        'Start'        => '2019-10-02',
                        'End'          => '2019-10-02',
                        'RatePlanCode' => RatePlanCode::SBX,
                        'InvTypeCode'  => 'ROOMID2'
                    ],
                    'RestrictionStatus'        => [
                        'Status' => 'Close',
                        'Restriction' => 'Arrival'
                    ],
                    'BookingLimit'             => 5
                ]
            ]
        ]
    ];
}
