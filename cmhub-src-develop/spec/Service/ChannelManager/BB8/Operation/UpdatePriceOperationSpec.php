<?php

namespace spec\App\Service\ChannelManager\BB8\Operation;

use App\Entity\Partner;
use App\Exception\AccessDeniedException;
use App\Model\ProductRateCollection;
use App\Security\Voter\BB8Voter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\BB8\Operation\UpdatePriceOperation;
use App\Service\ChannelManager\BB8\Serializer\ProductRateCollectionNormalizer;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UpdatePriceOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UpdatePriceOperation::class);
    }

    function let(
        BookingEngineInterface $bookingEngine,
        CmhubLogger $logger,
        ProductRateCollectionNormalizer $productRateCollectionNormalizer,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->beConstructedWith(
            $bookingEngine,
            $logger,
            $productRateCollectionNormalizer,
            $authorizationChecker
        );

    }

    function it_throw_access_denied_exception(
        ProductRateCollection $productRateCollection,
        ProductRateCollectionNormalizer $productRateCollectionNormalizer,
        AuthorizationCheckerInterface $authorizationChecker,
        Partner $partner,
        Request $request
    )
    {
        $data = '{"currencyCode":"EUR","date":"2019-03-22","price":99.9,"rateBandCode":"SBX","externalAccountId":"00019158","externalRoomId":"123ABC","externalCreatedAt":"2019-03-20T12:22:34.392Z","externalUpdatedAt":"2019-03-20T12:22:34.392Z"}';

        $request->getContent()->willReturn($data);

        $productRateCollectionNormalizer->denormalize(json_decode($data))->willReturn($productRateCollection);
        $productRateCollection->getPartner()->willReturn($partner);

        $authorizationChecker->isGranted(BB8Voter::BB8_OPERATION, $partner)->willReturn(false);
        $this->shouldThrow(AccessDeniedException::class)->during('handle', [$request]);
    }

    function it_handle_success(
        BookingEngineInterface $bookingEngine,
        ProductRateCollection $productRateCollection,
        CmhubLogger $logger,
        ProductRateCollectionNormalizer $productRateCollectionNormalizer,
        AuthorizationCheckerInterface $authorizationChecker,
        Partner $partner,
        Request $request
    )
    {
        $data = '{"currencyCode":"EUR","date":"2019-03-22","price":99.9,"rateBandCode":"SBX","externalAccountId":"00019158","externalRoomId":"123ABC","externalCreatedAt":"2019-03-20T12:22:34.392Z","externalUpdatedAt":"2019-03-20T12:22:34.392Z"}';

        $request->getContent()->willReturn($data);

        $productRateCollectionNormalizer->denormalize(json_decode($data))->willReturn($productRateCollection);
        $bookingEngine->updateRates($productRateCollection)->shouldBeCalled();
        $productRateCollection->getPartner()->willReturn($partner);
        $authorizationChecker->isGranted(BB8Voter::BB8_OPERATION, $partner)->willReturn(true);

        $logger->addOperationInfo(LogAction::UPDATE_RATES, $partner, $this)->shouldBeCalled();

        $this->handle($request)->shouldBe([]);
    }
}
