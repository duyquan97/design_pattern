<?php

namespace spec\App\Service\ChannelManager\AvailPro;

use App\Entity\Booking;
use App\Entity\Partner;
use App\Entity\Transaction;
use App\Exception\ChannelManagerClientException;
use App\Exception\CmHubException;
use App\Model\PushBooking;
use App\Service\ChannelManager\AvailPro\AvailProChannelManager;
use App\Utils\HttpClient;
use App\Utils\Monolog\CmhubLogger;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Twig\Environment;

class AvailProChannelManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailProChannelManager::class);
    }

    function let(HttpClient $availproHttpClient, Environment $templating, CmhubLogger $logger)
    {
        $this->beConstructedWith($availproHttpClient, $templating, $logger);
    }

    function it_support_channel()
    {
        $this->supports('dedge')->shouldBe(true);
    }

    function it_does_not_support()
    {
        $this->supports('wubook')->shouldBe(false);
    }

    function it_push_booking_success(PushBooking $pushBooking, Booking $booking, Partner $partner, Environment $templating,
        Transaction $transaction, ResponseInterface $response, StreamInterface $stream, HttpClient $availproHttpClient, CmhubLogger $logger)
    {
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getPartner()->willReturn($partner);
        $booking->getTransaction()->willReturn($transaction);
        $xml = 'xml_request';
        $templating->render(
            'Api/Ext/Xml/AvailPro/V1/GetBookings.xml.twig',
            [
                'partner'  => $partner,
                'bookings' => [$booking],
                'ratePlan' => 'SBX',
            ]
        )->willReturn($xml);

        $availproHttpClient->call('POST', $xml)->willReturn($response);
        $response->getBody()->willReturn($stream);
        $responseContent = '<message><success>OK</success></message>';
        $transaction->setResponse($responseContent)->shouldBeCalled();
        $transaction->setRequest($xml)->shouldBeCalled();
        $stream->getContents()->willReturn($responseContent);
        $logger->addPushBookingSent($pushBooking, $xml, $responseContent)->shouldBeCalled();

        $this->pushBookingRequest($pushBooking);
    }

    function it_get_invalid_response_while_push_booking(PushBooking $pushBooking, Booking $booking, Partner $partner, Environment $templating,
         Transaction $transaction, ResponseInterface $response, StreamInterface $stream, HttpClient $availproHttpClient, CmhubLogger $logger)
    {
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getPartner()->willReturn($partner);
        $booking->getTransaction()->willReturn($transaction);
        $xml = 'xml_request';
        $templating->render(
            'Api/Ext/Xml/AvailPro/V1/GetBookings.xml.twig',
            [
                'partner'  => $partner,
                'bookings' => [$booking],
                'ratePlan' => 'SBX',
            ]
        )->willReturn($xml);

        $availproHttpClient->call('POST', $xml)->willReturn($response);
        $response->getBody()->willReturn($stream);
        $responseContent = '<message><failure><comment><![CDATA[Unknown hotel 00545577 for booking RESA-0012026891]]></comment></failure></message>';
        $transaction->setResponse($responseContent)->shouldBeCalled();
        $transaction->setRequest($xml)->shouldBeCalled();
        $stream->getContents()->willReturn($responseContent);
        $logger->addPushBookingSent($pushBooking, $xml, $responseContent)->shouldBeCalled();
        $transaction->setResponse($responseContent)->shouldBeCalled();

        $this->shouldThrow(ChannelManagerClientException::class)->during('pushBookingRequest', [$pushBooking]);
    }

    function it_fail_to_push_booking(PushBooking $pushBooking, Booking $booking, Partner $partner, Environment $templating,
         Transaction $transaction, HttpClient $availproHttpClient, CmhubLogger $logger)
    {
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getPartner()->willReturn($partner);
        $booking->getTransaction()->willReturn($transaction);
        $xml = 'xml_request';
        $templating->render(
            'Api/Ext/Xml/AvailPro/V1/GetBookings.xml.twig',
            [
                'partner'  => $partner,
                'bookings' => [$booking],
                'ratePlan' => 'SBX',
            ]
        )->willReturn($xml);

        $request = new Request('POST', '/abc');
        $availproHttpClient->call('POST', $xml)->willThrow(
            new ClientException('error', $request)
        );
        $transaction->setResponse('error')->shouldBeCalled();
        $transaction->setRequest($xml)->shouldBeCalled();
        $logger->addPushBookingException('error', $pushBooking, $this)->shouldBeCalled();

        $this->shouldThrow(CmHubException::class)->during('pushBookingRequest', [$pushBooking]);
    }
}
