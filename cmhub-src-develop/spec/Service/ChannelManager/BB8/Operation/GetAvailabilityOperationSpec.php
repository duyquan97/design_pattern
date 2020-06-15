<?php

namespace spec\App\Service\ChannelManager\BB8\Operation;

use App\Entity\Partner;
use App\Entity\Product;
use App\Exception\PartnerNotFoundException;
use App\Exception\ValidationException;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductAvailabilityInterface;
use App\Model\ProductCollection;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\BB8\Operation\GetAvailabilityOperation;
use App\Service\ChannelManager\BB8\Serializer\AvailabilityCollectionNormalizer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetAvailabilityOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GetAvailabilityOperation::class);
    }

    function let(
        BookingEngineInterface $bookingEngine,
        AvailabilityCollectionNormalizer $availabilityCollectionNormalizer,
        CmhubLogger $logger,
        PartnerLoader $partnerLoader,
        ProductLoader $productLoader
    )
    {
        $this->beConstructedWith(
            $bookingEngine,
            $availabilityCollectionNormalizer,
            $logger,
            $partnerLoader,
            $productLoader
        );

    }

    function it_throw_validate_exception(Request $request) {
        $this->shouldThrow(ValidationException::class)->during('handle', [$request]);
    }

    function it_throw_bad_request_http_exception(Request $request) {
        $request->get('startDate')->willReturn('2019-03-20');
        $request->get('endDate')->willReturn('2019-03-22');
        $request->get('externalPartnerIds')->willReturn('');
        $this->shouldThrow(BadRequestHttpException::class)->during('handle', [$request]);
    }

    function it_throw_partner_not_found_exception(PartnerLoader $partnerLoader, Request $request) {
        $request->get('startDate')->willReturn('2019-03-20');
        $request->get('endDate')->willReturn('2019-03-22');
        $request->get('externalPartnerIds')->willReturn('00019158');
        $request->get('externalRoomIds')->willReturn('110224');
        $partnerLoader->findByIds(['00019158'])->willReturn([]);

        $this->shouldThrow(PartnerNotFoundException::class)->during('handle', [$request]);
    }

    function it_handle_success_with_one_partner_code_and_one_room_code(
        PartnerLoader $partnerLoader,
        Partner $partner,
        ProductLoader $productLoader,
        Product $product,
        BookingEngineInterface $bookingEngine,
        ProductAvailabilityCollectionInterface $availabilityCollection,
        ProductAvailabilityInterface $productAvailability,
        CmhubLogger $logger,
        AvailabilityCollectionNormalizer $availabilityCollectionNormalizer,
        Request $request
    )
    {
        $data = [
            "date" => '2019-03-20',
            "quantity" => 1,
            "externalRateBandId" => "SBX",
            "externalPartnerId" => "00019158",
            "externalRoomId" => "123ABC",
            "externalCreatedAt" => "2019-03-20T12:22:34.392Z",
            "externalUpdatedAt" => "2019-03-20T12:22:34.392Z"
        ];

        $request->get('startDate')->willReturn('2019-03-20');
        $request->get('endDate')->willReturn('2019-03-22');
        $request->get('externalPartnerIds')->willReturn('00019158');
        $request->get('externalRoomIds')->willReturn('110224');
        $partnerLoader->findByIds(['00019158'])->willReturn([$partner]);
        $productLoader->getProductsByRoomCode($partner, ['110224'])->willReturn([$product]);
        $bookingEngine->getAvailabilities(
            $partner,
            Argument::that(
                function(\DateTime $date) {
                    return $date->format('Y-m-d') === '2019-03-20';
                }
            ),
            Argument::that(
                function(\DateTime $date) {
                    return $date->format('Y-m-d') === '2019-03-22';
                }
            ),
            [$product]
        )->shouldBeCalled()->willReturn($availabilityCollection);

        $availabilityCollection->getProductAvailabilities()->shouldBeCalled()->willReturn([$productAvailability]);

        $logger->addOperationInfo(LogAction::GET_AVAILABILITY, null, $this)->shouldBeCalled();

        $availabilityCollectionNormalizer->normalize([$productAvailability])->shouldBeCalled()->willReturn($data);

        $this->handle($request)->shouldBe($data);
    }

    function it_handle_success_with_multi_partner_codes_and_one_room_code(
        PartnerLoader $partnerLoader,
        Partner $partner1,
        Partner $partner2,
        ProductLoader $productLoader,
        Product $product1,
        Product $product2,
        BookingEngineInterface $bookingEngine,
        ProductAvailabilityCollectionInterface $availabilityCollection1,
        ProductAvailabilityCollectionInterface $availabilityCollection2,
        ProductAvailabilityInterface $productAvailability1,
        ProductAvailabilityInterface $productAvailability2,
        CmhubLogger $logger,
        AvailabilityCollectionNormalizer $availabilityCollectionNormalizer,
        Request $request
    )
    {
        $data = [
            "date" => '2019-03-20',
            "quantity" => 1,
            "externalRateBandId" => "SBX",
            "externalPartnerId" => "00019158",
            "externalRoomId" => "123ABC",
            "externalCreatedAt" => "2019-03-20T12:22:34.392Z",
            "externalUpdatedAt" => "2019-03-20T12:22:34.392Z"
        ];

        $request->get('startDate')->willReturn('2019-03-20');
        $request->get('endDate')->willReturn('2019-03-22');
        $request->get('externalPartnerIds')->willReturn('00019158, 00145577');
        $request->get('externalRoomIds')->willReturn('110224');
        $partnerLoader->findByIds(['00019158', '00145577'])->willReturn([$partner1, $partner2]);
        $productLoader->getProductsByRoomCode($partner1, ['110224'])->willReturn([$product1]);
        $productLoader->getProductsByRoomCode($partner2, ['110224'])->willReturn([$product2]);
        $bookingEngine->getAvailabilities(
            $partner1,
            Argument::that(
                function(\DateTime $date) {
                    return $date->format('Y-m-d') === '2019-03-20';
                }
            ),
            Argument::that(
                function(\DateTime $date) {
                    return $date->format('Y-m-d') === '2019-03-22';
                }
            ),
            [$product1]
        )->shouldBeCalled()->willReturn($availabilityCollection1);
        $bookingEngine->getAvailabilities(
            $partner2,
            Argument::that(
                function(\DateTime $date) {
                    return $date->format('Y-m-d') === '2019-03-20';
                }
            ),
            Argument::that(
                function(\DateTime $date) {
                    return $date->format('Y-m-d') === '2019-03-22';
                }
            ),
            [$product2]
        )->shouldBeCalled()->willReturn($availabilityCollection2);

        $availabilityCollection1->getProductAvailabilities()->shouldBeCalled()->willReturn([$productAvailability1]);
        $availabilityCollection2->getProductAvailabilities()->shouldBeCalled()->willReturn([$productAvailability2]);

        $logger->addOperationInfo(LogAction::GET_AVAILABILITY, null, $this)->shouldBeCalled();

        $availabilityCollectionNormalizer->normalize([$productAvailability1, $productAvailability2])->shouldBeCalled()->willReturn($data);

        $this->handle($request)->shouldBe($data);
    }

    function it_handle_success_without_room_code(
        PartnerLoader $partnerLoader,
        Partner $partner,
        ProductLoader $productLoader,
        ProductCollection $productCollection,
        Product $product,
        BookingEngineInterface $bookingEngine,
        ProductAvailabilityCollectionInterface $availabilityCollection,
        ProductAvailabilityInterface $productAvailability,
        CmhubLogger $logger,
        AvailabilityCollectionNormalizer $availabilityCollectionNormalizer,
        Request $request
    )
    {
        $data = [
            "date" => '2019-03-20',
            "quantity" => 1,
            "externalRateBandId" => "SBX",
            "externalPartnerId" => "00019158",
            "externalRoomId" => "123ABC",
            "externalCreatedAt" => "2019-03-20T12:22:34.392Z",
            "externalUpdatedAt" => "2019-03-20T12:22:34.392Z"
        ];

        $request->get('startDate')->willReturn('2019-03-20');
        $request->get('endDate')->willReturn('2019-03-22');
        $request->get('externalPartnerIds')->willReturn('00019158');
        $request->get('externalRoomIds')->willReturn('');
        $partnerLoader->findByIds(['00019158'])->willReturn([$partner]);
        $productLoader->getByPartner($partner)->willReturn($productCollection);
        $productCollection->getProducts()->shouldBeCalled()->willReturn([$product]);
        $bookingEngine->getAvailabilities(
            $partner,
            Argument::that(
                function(\DateTime $date) {
                    return $date->format('Y-m-d') === '2019-03-20';
                }
            ),
            Argument::that(
                function(\DateTime $date) {
                    return $date->format('Y-m-d') === '2019-03-22';
                }
            ),
            [$product]
        )->shouldBeCalled()->willReturn($availabilityCollection);
        $availabilityCollection->getProductAvailabilities()->shouldBeCalled()->willReturn([$productAvailability]);

        $logger->addOperationInfo(LogAction::GET_AVAILABILITY, null, $this)->shouldBeCalled();

        $availabilityCollectionNormalizer->normalize([$productAvailability])->shouldBeCalled()->willReturn($data);

        $this->handle($request)->shouldBe($data);
    }
}
