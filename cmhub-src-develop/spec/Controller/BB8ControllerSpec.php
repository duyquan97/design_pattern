<?php

namespace spec\App\Controller;

use App\Controller\BB8Controller;
use App\Service\ChannelManager\BB8\BB8Integration;
use App\Service\ChannelManager\BB8\Operation\GetAvailabilityOperation;
use App\Service\ChannelManager\BB8\Operation\GetBookingsOperation;
use App\Service\ChannelManager\BB8\Operation\GetPriceOperation;
use App\Service\ChannelManager\BB8\Operation\GetRoomsOperation;
use App\Service\ChannelManager\BB8\Operation\UpdateAvailabilityOperation;
use App\Service\ChannelManager\BB8\Operation\UpdatePriceOperation;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BB8ControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BB8Controller::class);
    }

    function let(BB8Integration $integration, CmhubLogger $logger)
    {
        $this->beConstructedWith($integration, $logger);
    }

    function it_get_availabilities(JsonResponse $jsonResponse, Request $request, BB8Integration $integration)
    {
        $integration->handle($request, GetAvailabilityOperation::NAME)->shouldBeCalled()->willReturn('Availabilities data');
        $jsonResponse->setData('Availabilities data')->shouldBeCalled()->willReturn($jsonResponse);

        $this->getAvailabilityAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }

    function it_post_availabilities(JsonResponse $jsonResponse, Request $request, BB8Integration $integration)
    {
        $data = '[
          {
            "date": "2019-03-20",
            "quantity": 1,
            "type": "instant",
            "rateBandCode": "SBX",
            "externalPartnerId": "00019158",
            "externalRoomId": "110224",
            "externalCreatedAt": "2019-03-20T12:22:34.392Z",
            "externalUpdatedAt": "2019-03-20T12:22:34.392Z"
          }
        ]';

        $request->getContent()->willReturn($data);
        $integration->handle($request, UpdateAvailabilityOperation::NAME)->shouldBeCalled()->willReturn('Availabilities data');
        $jsonResponse->setData('Availabilities data')->shouldBeCalled()->willReturn($jsonResponse);

        $this->postAvailabilityAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }

    function it_get_rooms(JsonResponse $jsonResponse, Request $request, BB8Integration $integration, ParameterBag $parameterBag)
    {
        $request->getWrappedObject()->query = $parameterBag;

        $integration->handle($request, GetRoomsOperation::NAME)->shouldBeCalled()->willReturn('Rooms data');
        $jsonResponse->setData('Rooms data')->shouldBeCalled()->willReturn($jsonResponse);

        $this->getRoomsAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }

    function it_get_price(JsonResponse $jsonResponse, Request $request, BB8Integration $integration)
    {
        $integration->handle($request, GetPriceOperation::NAME)->shouldBeCalled()->willReturn('Prices data');
        $jsonResponse->setData('Prices data')->shouldBeCalled()->willReturn($jsonResponse);

        $this->getPricesAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }

    function it_post_price(JsonResponse $jsonResponse, Request $request, BB8Integration $integration)
    {
        $data = '[
          {
            "currencyCode": "EUR",
            "date": "2019-03-20",
            "price": 99.9,
            "rateBandId": "SBX",
            "roomId": "123ABC",
            "rateBandCode": "SBX",
            "externalPartnerId": "123ABC",
            "externalRoomId": "123ABC",
            "externalCreatedAt": "2019-03-20T12:22:34.392Z",
            "externalUpdatedAt": "2019-03-20T12:22:34.392Z"
          }
        ]';

        $request->getContent()->willReturn($data);
        $integration->handle($request, UpdatePriceOperation::NAME)->shouldBeCalled()->willReturn('Price data');
        $jsonResponse->setData('Price data')->shouldBeCalled()->willReturn($jsonResponse);

        $this->postPricesAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }

    function it_get_booking(JsonResponse $jsonResponse, Request $request, BB8Integration $integration)
    {
        $integration->handle($request, GetBookingsOperation::NAME)->shouldBeCalled()->willReturn('Bookings data');
        $jsonResponse->setData('Bookings data')->shouldBeCalled()->willReturn($jsonResponse);

        $this->getBookingsAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }

    function it_smoke_test(JsonResponse $jsonResponse)
    {
        $jsonResponse->setData()->shouldBeCalled()->willReturn($jsonResponse);
        $this->smokeTestAction($jsonResponse)->shouldBe($jsonResponse);
    }

    function it_throws_general_exception(JsonResponse $jsonResponse, Request $request, BB8Integration $integration)
    {
        $response = [
            "error" => "Unexpected internal error. Please contact administrator.",
        ];

        $exception = (new \Exception('Unexpected internal error. Please contact administrator.', Response::HTTP_INTERNAL_SERVER_ERROR));
        $integration->handle($request, 'get_availability')->shouldBeCalled()->willThrow($exception);
        $jsonResponse->setData($response)->shouldBeCalled()->willReturn($jsonResponse);
        $jsonResponse->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)->shouldBeCalled()->willReturn($jsonResponse);

        $this->getAvailabilityAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }
}
