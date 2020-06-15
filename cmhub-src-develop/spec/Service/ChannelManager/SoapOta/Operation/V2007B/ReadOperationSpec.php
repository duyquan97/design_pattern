<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Entity\ChannelManager;
use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\ValidationException;
use App\Model\BookingCollection;
use App\Model\PartnerInterface;
use App\Model\OTADateType;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Operation\V2007B\ReadOperation;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\Loader\PartnerLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ReadOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReadOperation::class);
    }

    function let(
        CmhubLogger $logger,
        BookingEngineInterface $bookingEngine,
        SoapSerializer $soapSerializer,
        PartnerLoader $partnerLoader,
        AuthorizationCheckerInterface $authorizationChecker,
        PartnerInterface $partner,
        ChannelManager $channelManager
    )
    {
        $this->beConstructedWith($bookingEngine, $soapSerializer, $partnerLoader, $authorizationChecker, $logger);

        $partnerLoader->find('hotel_id')->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('cm_identifier');
        $partner->getIdentifier()->willReturn('partner_id');
        $partner->getName()->willReturn('partner_name');

        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);
    }

    function it_throws_access_denied_exception_if_partner_is_not_granted(PartnerInterface $partner, AuthorizationCheckerInterface $authorizationChecker)
    {
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(false);
        $this->shouldThrow(AccessDeniedException::class)->during('handle', [json_decode(json_encode($this->testData))]);
    }

    function it_throws_invalid_parameter_if_wrong_value_for_date_type()
    {
        $data = json_decode(json_encode($this->testData));
        $data->ReadRequests->HotelReadRequest->SelectionCriteria->Start = '2018-01-20';
        $data->ReadRequests->HotelReadRequest->SelectionCriteria->End = '2018-01-10';
        $data->ReadRequests->HotelReadRequest->SelectionCriteria->DateType = 'WrongType';
        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }

    function it_throws_exception_when_date_is_not_iso8601_format()
    {
        $data = json_decode(json_encode($this->testData));
        $data->ReadRequests->HotelReadRequest->SelectionCriteria->Start = '20-10-2018';
        $this->shouldThrow(DateFormatException::class)->during('handle', [$data]);
    }

    function it_throws_exception_if_start_date_is_greater_than_end_date()
    {
        $data = json_decode(json_encode($this->testData));
        $data->ReadRequests->HotelReadRequest->SelectionCriteria->Start = '2018-01-20';
        $data->ReadRequests->HotelReadRequest->SelectionCriteria->End = '2018-01-10';
        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }

    function it_handles_operation(
        BookingEngineInterface $bookingEngine,
        PartnerInterface $partner,
        SoapSerializer $soapSerializer,
        BookingCollection $bookingCollection,
        CmhubLogger $logger
    )
    {
        $bookingEngine
            ->getBookings(
                Argument::that(
                    function (\DateTime $start) {
                        return '2019-09-01' === $start->format('Y-m-d');
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return '2019-09-11' === $end->format('Y-m-d');
                    }
                ),
                null,
                [$partner],
                OTADateType::LAST_UPDATE_DATE
            )
            ->willReturn($bookingCollection);

        $soapSerializer->normalize($bookingCollection)->willReturn($result = ['the' => 'normalized data']);

        $logger->addOperationInfo(LogAction::GET_BOOKINGS, $partner, $this)->shouldBeCalled();
        $this->handle(json_decode(json_encode($this->testData)))->shouldBe($result);
    }

    protected $testData = [
        'ReadRequests' => [
            'HotelReadRequest' => [
                'SelectionCriteria' => [
                    'DateType' => 'LastUpdateDate',
                    'Start'    => '2019-09-01',
                    'End'      => '2019-09-11',
                ],
                'HotelCode'         => 'hotel_id'
            ]
        ]
    ];
}
