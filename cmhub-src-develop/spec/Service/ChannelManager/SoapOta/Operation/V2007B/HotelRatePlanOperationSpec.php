<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Entity\ChannelManager;
use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\ValidationException;
use App\Model\PartnerInterface;
use App\Model\ProductRateCollection;
use App\Model\RatePlanCode;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Operation\V2007B\HotelRatePlanOperation;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\Loader\PartnerLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HotelRatePlanOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(HotelRatePlanOperation::class);
    }

    function let(
        BookingEngineInterface $bookingEngine,
        SoapSerializer $soapSerializer,
        AuthorizationCheckerInterface $authorizationChecker,
        PartnerInterface $partner,
        PartnerLoader $partnerLoader,
        CmhubLogger $logger,
        ChannelManager $channelManager
    )
    {
        $this->beConstructedWith($bookingEngine, $partnerLoader, $soapSerializer, $authorizationChecker, $logger);

        $partnerLoader->find('partner_id')->willReturn($partner);
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

    function it_throws_exception_if_wrong_date_format()
    {
        $data = json_decode(json_encode($this->testData));
        $data->RatePlans->RatePlan->DateRange->Start = '20-10-2018';
        $this->shouldThrow(DateFormatException::class)->during('handle', [$data]);
    }

    function it_throws_exception_start_date_is_greater_than_end_date()
    {
        $data = json_decode(json_encode($this->testData));
        $data->RatePlans->RatePlan->DateRange->Start = '2018-10-20';
        $data->RatePlans->RatePlan->DateRange->End = '2018-10-10';
        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }

    function it_handles_operation(SoapSerializer $soapSerializer, BookingEngineInterface $bookingEngine, PartnerInterface $partner, ProductRateCollection $rateCollection, CmhubLogger $logger)
    {
        $bookingEngine
            ->getRates(
                $partner,
                Argument::that(
                    function (\DateTime $start) {
                        return '2018-01-01' === $start->format('Y-m-d');
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return '2018-01-11' === $end->format('Y-m-d');
                    }
                )
            )
            ->willReturn($rateCollection);

        $soapSerializer->normalize($rateCollection)->willReturn(['normalized' => 'data']);
        $logger->addOperationInfo(LogAction::GET_RATES, $partner, $this)->shouldBeCalled();

        $this->handle(json_decode(json_encode($this->testData)))->shouldBe(['normalized' => 'data']);
    }

    protected $testData = [
        'RatePlans' => [
            'RatePlan' => [
                'DateRange'          => [
                    'Start' => '2018-01-01',
                    'End'   => '2018-01-11',
                ],
                'RatePlanCandidates' => [
                    'RatePlanCandidate' => [
                        'RatePlanCode' => RatePlanCode::SBX
                    ]
                ],
                'HotelRef'           => [
                    'HotelCode' => 'partner_id'
                ]
            ]
        ]
    ];
}
