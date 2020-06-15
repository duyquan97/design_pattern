<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Entity\ChannelManager;
use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\ValidationException;
use App\Exception\ProductNotFoundException;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductInterface;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Operation\V2007B\HotelInvCountOperation;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HotelInvCountOperationSpec extends ObjectBehavior
{
    protected $testData = [
        'HotelInvCountRequests' => [
            'HotelInvCountRequest' => [
                'DateRange' => [
                    'Start' => '2018-08-01',
                    'End'   => '2018-08-10',
                ],
                'RoomTypeCandidates' => [
                    'RoomTypeCandidate' => [
                        [
                            'RoomTypeCode' => 'ROOMID1',
                        ],
                        [
                            'RoomTypeCode' => 'ROOMID2',
                        ],
                    ],
                ],
                'HotelRef' => [
                    'HotelCode' => 'hotel_id',
                ],
            ],
        ],
    ];

    function it_is_initializable()
    {
        $this->shouldHaveType(HotelInvCountOperation::class);
    }

    function let(
        BookingEngineInterface $bookingEngine,
        ProductLoader $productLoader,
        SoapSerializer $soapSerializer,
        PartnerInterface $partner,
        ProductInterface $product,
        ProductInterface $product1,
        PartnerLoader $partnerLoader,
        AuthorizationCheckerInterface $authorizationChecker,
        CmhubLogger $logger,
        ChannelManager $channelManager
    ) {
        $this->beConstructedWith($bookingEngine, $productLoader, $partnerLoader, $soapSerializer, $authorizationChecker, $logger);

        $partnerLoader->find('hotel_id')->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('cm_identifier');
        $partner->getIdentifier()->willReturn('partner_id');
        $partner->getName()->willReturn('partner_name');
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);
        $product->getIdentifier()->willReturn('ROOMID1');
        $product1->getIdentifier()->willReturn('ROOMID2');
    }

    function it_throws_access_denied_exception_if_partner_is_not_granted(AuthorizationCheckerInterface $authorizationChecker, PartnerInterface $partner)
    {
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->shouldBeCalled()->willReturn(false);
        $this->shouldThrow(AccessDeniedException::class)->during('handle', [json_decode(json_encode($this->testData))]);
    }

    function it_throws_exception_if_wrong_date_format()
    {
        $data = json_decode(json_encode($this->testData));
        $data->HotelInvCountRequests->HotelInvCountRequest->DateRange->Start = '20-10-2039';
        $this->shouldThrow(DateFormatException::class)->during('handle', [$data]);
    }

    function it_throws_exception_if_start_date_is_greater_than_end_date()
    {
        $data = json_decode(json_encode($this->testData));
        $data->HotelInvCountRequests->HotelInvCountRequest->DateRange->Start = '2018-01-20';
        $data->HotelInvCountRequests->HotelInvCountRequest->DateRange->End = '2018-01-10';
        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }

    function it_throws_exception_if_a_product_is_not_found(
        PartnerInterface $partner,
        ProductInterface $product,
        ProductInterface $product1,
        ProductLoader $productLoader
    ) {
        $data = json_decode(json_encode($this->testData));
        $data->HotelInvCountRequests->HotelInvCountRequest->RoomTypeCandidates->RoomTypeCandidate[0]->RoomTypeCode = 'ROOMID1';
        $data->HotelInvCountRequests->HotelInvCountRequest->RoomTypeCandidates->RoomTypeCandidate[1]->RoomTypeCode = 'ROOMNAN';
        $productLoader
            ->getProductsByRoomCode(
                $partner,
                ['ROOMID1', 'ROOMNAN']
            )
            ->willReturn(
                [
                    $product,
                    $product1,
                ]
            );
        $this->shouldThrow(ProductNotFoundException::class)->during('handle', [$data]);
    }

    function it_handles_operation(
        BookingEngineInterface $bookingEngine,
        PartnerInterface $partner,
        ProductInterface $product,
        ProductInterface $product1,
        ProductAvailabilityCollectionInterface $availabilityCollection,
        SoapSerializer $soapSerializer,
        ProductLoader $productLoader,
        CmhubLogger $logger
    ) {
        $bookingEngine
            ->getAvailabilities(
                $partner,
                Argument::that(
                    function (\DateTime $start) {
                        return '2018-08-01' === $start->format('Y-m-d');
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return '2018-08-10' === $end->format('Y-m-d');
                    }
                ),
                [
                    $product,
                    $product1
                ]
            )
            ->willReturn($availabilityCollection);

        $productLoader
            ->getProductsByRoomCode(
                $partner,
                ['ROOMID1', 'ROOMID2']
            )
            ->willReturn(
                [
                    $product,
                    $product1,
                ]
            );
        $soapSerializer->normalize($availabilityCollection)->willReturn(['the' => 'data']);
        $logger->addOperationInfo(LogAction::GET_AVAILABILITY, $partner, $this)->shouldBeCalled();
        $this->handle(json_decode(json_encode($this->testData)))->shouldBe(['the' => 'data']);
    }
}
