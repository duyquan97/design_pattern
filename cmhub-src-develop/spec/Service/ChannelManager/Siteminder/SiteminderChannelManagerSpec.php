<?php

namespace spec\App\Service\ChannelManager\Siteminder;

use App\Entity\Booking;
use App\Entity\Transaction;
use App\Exception\ChannelManagerClientException;
use App\Exception\CmHubException;
use App\Booking\Model\BookingStatus;
use App\Model\CredentialInterface;
use App\Model\Guest;
use App\Model\PushBooking;
use App\Service\ChannelManager\Siteminder\SiteminderChannelManager;
use App\Utils\HttpClient;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Twig\Environment;

class SiteminderChannelManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SiteminderChannelManager::class);
    }

    function let(
        HttpClient $httpClient,
        CredentialInterface $credentials,
        Environment $templating,
        CmhubLogger $logger
    )
    {
        $this->beConstructedWith($httpClient, $credentials, $templating, $logger);
    }

    function it_responds_on_success(
        HttpClient $httpClient,
        CmhubLogger $logger,
        PushBooking $pushBooking,
        Environment $templating,
        Booking $booking,
        Guest $guest,
        ResponseInterface $response,
        StreamInterface $content,
        CredentialInterface $credentials,
        Transaction $transaction
    )
    {
        $xml = 'xml_data';
        $responseString = '<?xml version="1.0" encoding="utf-8"?>
            <OTA_HotelResNotifRS>
                <Success />
            </OTA_HotelResNotifRS>';

        $credentials->getUsername()->willReturn('username');
        $credentials->getPassword()->willReturn('password');

        $templating->render(Argument::type('string'), Argument::type('array'))->willReturn($xml);
        $response->getBody()->willReturn($content);
        $content->getContents()->willReturn($responseString);
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getGuests()->willReturn([$guest]);
        $booking->getStatus()->willReturn(BookingStatus::CONFIRMED);
        $httpClient->call('POST', $xml)->willReturn($response);
        $booking->getTransaction()->willReturn($transaction);
        $transaction->setRequest($xml)->shouldBeCalled()->willReturn($transaction);
        $transaction->setResponse($responseString)->shouldBeCalled()->willReturn($transaction);
        $logger->addPushBookingSent($pushBooking, Argument::type('string'), $responseString)->shouldBeCalled();
        $this->pushBookingRequest($pushBooking);
    }

    function it_throws_exception_on_wrong_format_response_when_push_booking(
        HttpClient $httpClient,
        Environment $templating,
        CmhubLogger $logger,
        PushBooking $pushBooking,
        Booking $booking,
        Guest $guest,
        ResponseInterface $response,
        StreamInterface $content,
        CredentialInterface $credentials,
        Transaction $transaction
    )
    {
        $xml = 'xml_data';
        $responseString = 'wrong_format_response';

        $credentials->getUsername()->willReturn('username');
        $credentials->getPassword()->willReturn('password');
        $templating->render(Argument::type('string'), Argument::type('array'))->willReturn($xml);
        $response->getBody()->willReturn($content);
        $response->getStatusCode()->willReturn(500);
        $content->getContents()->willReturn($responseString);
        $httpClient->call('POST', $xml)->willReturn($response);
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getGuests()->willReturn([$guest]);
        $booking->getStatus()->willReturn(BookingStatus::CONFIRMED);
        $booking->getTransaction()->willReturn($transaction);
        $transaction->setRequest($xml)->shouldBeCalled();
        $transaction->setResponse($responseString)->willReturn($transaction);
        $logger->addPushBookingSent($pushBooking, Argument::type('string'), $responseString)->shouldBeCalled();
        $logger->addPushBookingException('wrong_format_response', $pushBooking, $this, $xml)->shouldBeCalled();

        $this->shouldThrow(CmhubException::class)->during('pushBookingRequest', [$pushBooking]);
    }

    function it_throws_exception_on_error_and_logs_response_when_push_booking(
        HttpClient $httpClient,
        Environment $templating,
        CmhubLogger $logger,
        PushBooking $pushBooking,
        Booking $booking,
        Guest $guest,
        ResponseInterface $response,
        StreamInterface $content,
        CredentialInterface $credentials,
        Transaction $transaction
    )
    {
        $xml = 'xml_data';
        $responseString = '<?xml version="1.0" encoding="utf-8"?>
        <OTA_HotelResNotifRS>
            <Errors>
                <Error Code="11">
                    <Text>error message</Text>
                </Error>
            </Errors>
        </OTA_HotelResNotifRS>';

        $credentials->getUsername()->willReturn('username');
        $credentials->getPassword()->willReturn('password');

        $templating->render(Argument::type('string'), Argument::type('array'))->willReturn($xml);
        $response->getBody()->willReturn($content);
        $response->getStatusCode()->willReturn(400);
        $content->getContents()->willReturn($responseString);
        $httpClient->call('POST', $xml)->willReturn($response);
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getGuests()->willReturn([$guest]);
        $booking->getStatus()->willReturn(BookingStatus::CONFIRMED);
        $booking->getTransaction()->willReturn($transaction);
        $transaction->setRequest($xml)->shouldBeCalled();
        $transaction->setResponse($responseString)->willReturn($transaction);
        $logger->addPushBookingSent($pushBooking, Argument::type('string'), $responseString)->shouldBeCalled();
        $logger->addPushBookingException($responseString, $pushBooking, $this, $xml)->shouldBeCalled();

        $this->shouldThrow(ChannelManagerClientException::class)->during('pushBookingRequest', [$pushBooking]);
    }
}
