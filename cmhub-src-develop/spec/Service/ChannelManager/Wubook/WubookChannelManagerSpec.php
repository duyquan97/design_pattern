<?php

namespace spec\App\Service\ChannelManager\Wubook;

use App\Entity\Booking;
use App\Entity\Partner;
use App\Entity\Transaction;
use App\Exception\ChannelManagerClientException;
use App\Model\PushBooking;
use App\Service\ChannelManager\Wubook\Serializer\BookingNormalizer;
use App\Service\ChannelManager\Wubook\WubookChannelManager;
use App\Utils\Monolog\CmhubLogger;
use GuzzleHttp\Client as Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class WubookChannelManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(WubookChannelManager::class);
    }

    function let(BookingNormalizer $bookingNormalizer, CmhubLogger $logger, Client $client)
    {
        $endpoint = "wubook_endpoint";
        $this->beConstructedWith($bookingNormalizer, $logger, $client, $endpoint);
    }

    function it_push_bookings(BookingNormalizer $bookingNormalizer, Client $client, Booking $booking, PushBooking $pushBooking,
      Partner $partner, ResponseInterface $response, Transaction $transaction, StreamInterface $body, CmhubLogger $logger)
    {
        $endpoint = "wubook_endpoint";
        $request = [
            "foo" => "bar",
        ];

        $pushBooking->getBooking()->willReturn($booking);
        $booking->getPartner()->willReturn($partner);
        $booking->getTransaction()->willReturn($transaction);
        $transaction->setRequest('{"foo":"bar"}')->shouldBeCalled();
        $partner->getIdentifier()->willReturn('00123456');
        $bookingNormalizer->normalize($booking)->willReturn($request);

        $client->post(
            sprintf("%s%s", $endpoint, '00123456'),
            [
                "json" => $request
            ]
        )->willReturn($response);
        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn("response");
        $transaction->setResponse("response")->shouldBeCalled();
        $logger->addPushBookingSent($pushBooking, '{"foo":"bar"}', 'response')->shouldBeCalled();

        $this->pushBookingRequest($pushBooking);
    }

    function it_push_non_transaction_bookings(BookingNormalizer $bookingNormalizer, Client $client, Booking $booking, PushBooking $pushBooking,
          Partner $partner, ResponseInterface $response, StreamInterface $body, CmhubLogger $logger)
    {
        $endpoint = "wubook_endpoint";
        $request = [
            "foo" => "bar",
        ];

        $pushBooking->getBooking()->willReturn($booking);
        $booking->getPartner()->willReturn($partner);
        $booking->getTransaction()->willReturn(null);
        $partner->getIdentifier()->willReturn('00123456');
        $bookingNormalizer->normalize($booking)->willReturn($request);

        $client->post(
            sprintf("%s%s", $endpoint, '00123456'),
            [
                "json" => $request
            ]
        )->willReturn($response);
        $response->getBody()->willReturn($body);
        $body->getContents()->willReturn("response");
        $logger->addPushBookingSent($pushBooking, '{"foo":"bar"}', 'response')->shouldBeCalled();

        $this->pushBookingRequest($pushBooking);
    }

    function it_throw_exception_when_push_bookings(BookingNormalizer $bookingNormalizer, Client $client, Booking $booking, PushBooking $pushBooking,
      Partner $partner, ResponseInterface $response, Transaction $transaction, StreamInterface $body, CmhubLogger $logger)
    {
        $endpoint = "wubook_endpoint";
        $request = [
            "foo" => "bar",
        ];

        $pushBooking->getBooking()->willReturn($booking);
        $booking->getPartner()->willReturn($partner);
        $booking->getTransaction()->willReturn($transaction);
        $transaction->setRequest('{"foo":"bar"}')->shouldBeCalled();
        $partner->getIdentifier()->willReturn('00123456');
        $bookingNormalizer->normalize($booking)->willReturn($request);

        $client->post(
            sprintf("%s%s", $endpoint, '00123456'),
            [
                "json" => $request
            ]
        )->willThrow(new \Exception('exception'));

        $transaction->setResponse('exception')->shouldBeCalled();
        $logger->addPushBookingException('exception', $pushBooking, $this, '{"foo":"bar"}')->shouldBeCalled();

        $this->shouldThrow(ChannelManagerClientException::class)->during('pushBookingRequest', [$pushBooking]);
    }

    function it_supports_channel_manager()
    {
        $this->supports(WubookChannelManager::NAME)->shouldBe(true);
    }

    function it_doesnt_support_channel_manager()
    {
        $this->supports('whatever')->shouldBe(false);
    }
}
