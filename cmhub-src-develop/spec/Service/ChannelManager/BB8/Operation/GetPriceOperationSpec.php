<?php

namespace spec\App\Service\ChannelManager\BB8\Operation;

use App\Entity\Partner;
use App\Entity\Product;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\ProductCollection;
use App\Model\ProductRate;
use App\Model\ProductRateCollection;
use App\Model\ProductRateInterface;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\BB8\Operation\GetPriceOperation;
use App\Service\ChannelManager\BB8\Serializer\ProductRateCollectionNormalizer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetPriceOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GetPriceOperation::class);
    }

    function let(
        BookingEngineInterface $bookingEngine,
        ProductRateCollectionNormalizer $productRateCollectionNormalizer,
        ProductLoader $productLoader,
        PartnerLoader $partnerLoader,
        CmhubLogger $logger
    )
    {
        $this->beConstructedWith(
            $bookingEngine,
            $productRateCollectionNormalizer,
            $productLoader,
            $partnerLoader,
            $logger
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

    function it_throw_partner_not_found_exception(Request $request, PartnerLoader $partnerLoader) {
        $request->get('startDate')->willReturn('2019-03-20');
        $request->get('endDate')->willReturn('2019-03-22');
        $request->get('externalPartnerIds')->willReturn('1345467');
        $request->get('externalRoomIds')->willReturn('110224');
        $partnerLoader->findByIds(['1345467'])->willReturn([]);

        $this->shouldThrow(PartnerNotFoundException::class)->during('handle', [$request]);
    }

    function it_handle_success_with_one_partner_code_and_without_room_code(
        PartnerLoader $partnerLoader,
        Partner $partner,
        ProductLoader $productLoader,
        Product $product1,
        Product $product2,
        BookingEngineInterface $bookingEngine,
        ProductRateCollection $productRateCollection,
        ProductRateInterface $productRate1,
        ProductRateInterface $productRate2,
        CmhubLogger $logger,
        ProductRateCollectionNormalizer $productRateCollectionNormalizer,
        Request $request,
        ProductCollection $productCollection
    )
    {
        $data = [
            "currencyCode" => "EUR",
            "date" => "2019-03-22",
            "amount" => 900,
            "rateBandCode" => "SBX",
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

        $productCollection->getProducts()->shouldBeCalled()->willReturn([$product1, $product2]);
        $bookingEngine->getRates(
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
            [$product1, $product2]
        )->shouldBeCalled()->willReturn($productRateCollection);

        $productRateCollection->getProductRates()->shouldBeCalled()->willReturn([$productRate1, $productRate2]);

        $logger->addOperationInfo(LogAction::GET_PRICES, null, $this)->shouldBeCalled();

        $productRateCollectionNormalizer->normalize([$productRate1, $productRate2])->shouldBeCalled()->willReturn($data);

        $this->handle($request)->shouldBe($data);
    }

    function it_handle_success_with_one_partner_code_and_one_room_code(
        PartnerLoader $partnerLoader,
        Partner $partner,
        ProductLoader $productLoader,
        Product $product,
        BookingEngineInterface $bookingEngine,
        ProductRateCollection $productRateCollection,
        ProductRateInterface $productRate1,
        ProductRateInterface $productRate2,
        CmhubLogger $logger,
        ProductRateCollectionNormalizer $productRateCollectionNormalizer,
        Request $request
    )
    {
        $data = [
            "currencyCode" => "EUR",
            "date" => "2019-03-22",
            "amount" => 900,
            "rateBandCode" => "SBX",
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
        $bookingEngine->getRates(
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
        )->shouldBeCalled()->willReturn($productRateCollection);

        $productRateCollection->getProductRates()->shouldBeCalled()->willReturn([$productRate1, $productRate2]);

        $logger->addOperationInfo(LogAction::GET_PRICES, null, $this)->shouldBeCalled();

        $productRateCollectionNormalizer->normalize([$productRate1, $productRate2])->shouldBeCalled()->willReturn($data);

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
        ProductRateCollection $productRateCollection1,
        ProductRateCollection $productRateCollection2,
        ProductRateInterface $productRate1,
        ProductRateInterface $productRate2,
        ProductRateInterface $productRate3,
        CmhubLogger $logger,
        ProductRateCollectionNormalizer $productRateCollectionNormalizer,
        Request $request
    )
    {
        $data = [
            "currencyCode" => "EUR",
            "date" => "2019-03-22",
            "amount" => 900,
            "rateBandCode" => "SBX",
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
        $bookingEngine->getRates(
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
        )->shouldBeCalled()->willReturn($productRateCollection1);
        $bookingEngine->getRates(
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
        )->shouldBeCalled()->willReturn($productRateCollection2);

        $productRateCollection1->getProductRates()->shouldBeCalled()->willReturn([$productRate1]);
        $productRateCollection2->getProductRates()->shouldBeCalled()->willReturn([$productRate2, $productRate3]);

        $logger->addOperationInfo(LogAction::GET_PRICES, null, $this)->shouldBeCalled();

        $productRateCollectionNormalizer->normalize([$productRate1, $productRate2, $productRate3])->shouldBeCalled()->willReturn($data);

        $this->handle($request)->shouldBe($data);
    }

}
