<?php

namespace spec\App\Service\ChannelManager\BB8\Operation;

use App\Entity\Partner;
use App\Exception\AccessDeniedException;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Security\Voter\BB8Voter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\BB8\Operation\UpdateAvailabilityOperation;
use App\Service\ChannelManager\BB8\Serializer\AvailabilityCollectionNormalizer;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UpdateAvailabilityOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UpdateAvailabilityOperation::class);
    }

    function let(
        BookingEngineInterface $bookingEngine,
        CmhubLogger $logger,
        AvailabilityCollectionNormalizer $availabilityCollectionNormalizer,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->beConstructedWith(
            $bookingEngine,
            $logger,
            $availabilityCollectionNormalizer,
            $authorizationChecker
        );

    }
    function it_throw_access_denied_exception(
        ProductAvailabilityCollectionInterface $availabilityCollection,
        AvailabilityCollectionNormalizer $availabilityCollectionNormalizer,
        AuthorizationCheckerInterface $authorizationChecker,
        Partner $partner,
        Request $request
    )
    {
        $data = '{"date":"2019-03-20","quantity":1,"externalRateBandId":"SBX","externalPartnerId":"00019158","externalRoomId":"123ABC","externalCreatedAt":"2019-03-20T12:22:34.392Z","externalUpdatedAt":"2019-03-20T12:22:34.392Z"}';

        $request->getContent()->willReturn($data);

        $availabilityCollectionNormalizer->denormalize(json_decode($data))->willReturn($availabilityCollection);
        $availabilityCollection->getPartner()->shouldBeCalled()->willReturn($partner);
        $authorizationChecker->isGranted(BB8Voter::BB8_OPERATION, $partner)->willReturn(false);

        $this->shouldThrow(AccessDeniedException::class)->during('handle', [$request]);
    }

    function it_handle_success(
        BookingEngineInterface $bookingEngine,
        ProductAvailabilityCollectionInterface $availabilityCollection,
        CmhubLogger $logger,
        AvailabilityCollectionNormalizer $availabilityCollectionNormalizer,
        AuthorizationCheckerInterface $authorizationChecker,
        Partner $partner,
        Request $request
    )
    {
        $data = '{"date":"2019-03-20","quantity":1,"externalRateBandId":"SBX","externalPartnerId":"00019158","externalRoomId":"123ABC","externalCreatedAt":"2019-03-20T12:22:34.392Z","externalUpdatedAt":"2019-03-20T12:22:34.392Z"}';

        $request->getContent()->willReturn($data);

        $availabilityCollectionNormalizer->denormalize(json_decode($data))->willReturn($availabilityCollection);
        $bookingEngine->updateAvailability($availabilityCollection)->shouldBeCalled()->willReturn($availabilityCollection);
        $availabilityCollection->getProductAvailabilities()->shouldBeCalled();
        $availabilityCollection->getPartner()->shouldBeCalled()->willReturn($partner);
        $authorizationChecker->isGranted(BB8Voter::BB8_OPERATION, $partner)->willReturn(true);

        $logger->addOperationInfo(LogAction::UPDATE_AVAILABILITY, $partner, $this)->shouldBeCalled();

        $this->handle($request)->shouldBe([]);
    }
}
