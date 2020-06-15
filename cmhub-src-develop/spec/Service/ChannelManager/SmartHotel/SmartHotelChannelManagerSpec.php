<?php

namespace spec\App\Service\ChannelManager\SmartHotel;

use App\Entity\Booking;
use App\Entity\BookingProduct;
use App\Entity\Guest;
use App\Entity\Product;
use App\Entity\Transaction;
use App\Exception\CmHubException;
use App\Booking\Model\BookingStatus;
use App\Model\Credential;
use App\Model\CredentialInterface;
use App\Model\GuestInterface;
use App\Model\ProductInterface;
use App\Model\PushBooking;
use App\Model\RatePlanCode;
use App\Service\ChannelManager\SmartHotel\SmartHotelChannelManager;
use App\Utils\HttpClient;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Twig\Environment;

class SmartHotelChannelManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SmartHotelChannelManager::class);
    }

    function let(
        HttpClient $httpClient,
        Environment $templating,
        CmhubLogger $logger
    )
    {
        $credentials = new Credential('username', 'password');

        $this->beConstructedWith($httpClient, $credentials, $templating, $logger);
    }

    function it_push_booking_success(
        HttpClient $httpClient,
        Environment $templating,
        PushBooking $pushBooking,
        Booking $booking,
        Guest $guest,
        ResponseInterface $response,
        StreamInterface $content,
        CmhubLogger $logger,
        Transaction $transaction
    )
    {
        $xml = 'xml_data';
        $responseString = '<?xml version="1.0" encoding="utf-8"?>
            <OTA_HotelResNotifRS>
                <Success />
            </OTA_HotelResNotifRS>';

        $templating->render(Argument::type('string'), Argument::type('array'))->willReturn($xml);
        $response->getBody()->willReturn($content);
        $content->getContents()->willReturn($responseString);

        $pushBooking->getBooking()->willReturn($booking);
        $booking->getTransaction()->willReturn($transaction);
        $transaction->setRequest($xml)->shouldBeCalled();
        $booking->getGuests()->willReturn([$guest]);
        $booking->getStatus()->willReturn(BookingStatus::CONFIRMED);

        $httpClient->call('POST', $xml, [], 'OTA_HotelResNotifRQ.aspx')->willReturn($response);
        $transaction->setResponse($responseString)->shouldBeCalled();
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
        Transaction $transaction
    )
    {
        $xml = 'xml_data';
        $responseString = 'wrong_format_response';

        $templating->render(Argument::type('string'), Argument::type('array'))->willReturn($xml);
        $response->getBody()->willReturn($content);
        $content->getContents()->willReturn($responseString);
        $httpClient->call('POST', $xml, [], 'OTA_HotelResNotifRQ.aspx')->willReturn($response);
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getGuests()->willReturn([$guest]);
        $booking->getStatus()->willReturn(BookingStatus::CONFIRMED);
        $booking->getTransaction()->willReturn($transaction);
        $transaction->setRequest($xml)->shouldBeCalled();
        $transaction->setResponse($responseString)->shouldBeCalled();
        $logger->addPushBookingSent($pushBooking, Argument::type('string'), $responseString)->shouldBeCalled();
        $logger->addPushBookingException('wrong format response "' . $responseString . '"', $pushBooking, $this, $xml)->shouldBeCalled();

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

        $templating->render(Argument::type('string'), Argument::type('array'))->willReturn($xml);
        $response->getBody()->willReturn($content);
        $response->getStatusCode()->willReturn(500);
        $content->getContents()->willReturn($responseString);
        $httpClient->call('POST', $xml, [], 'OTA_HotelResNotifRQ.aspx')->willReturn($response);

        $pushBooking->getBooking()->willReturn($booking);
        $booking->getGuests()->willReturn([$guest]);
        $booking->getStatus()->willReturn(BookingStatus::CONFIRMED);
        $booking->getTransaction()->willReturn($transaction);
        $transaction->setRequest($xml)->shouldBeCalled();
        $transaction->setResponse($responseString)->shouldBeCalled();

        $logger->addPushBookingSent($pushBooking, Argument::type('string'), $responseString)->shouldBeCalled();
        $logger->addPushBookingException('error message', $pushBooking, $this, $xml)->shouldBeCalled();

        $this->shouldThrow(CmHubException::class)->during('pushBookingRequest', [$pushBooking]);
    }

    function it_cancel_booking_success(
        HttpClient $httpClient,
        Environment $templating,
        PushBooking $pushBooking,
        Booking $booking,
        BookingProduct $firstBooking,
        Product $product,
        ResponseInterface $response,
        StreamInterface $content,
        CmhubLogger $logger,
        Transaction $transaction
    )
    {
        $xml = 'xml_data';
        $responseString = '<?xml version="1.0" encoding="utf-8"?>
            <OTA_CancelRS>
                <Success />
            </OTA_CancelRS>';

        $templating->render(Argument::type('string'), Argument::type('array'))->willReturn($xml);
        $response->getBody()->willReturn($content);
        $content->getContents()->willReturn($responseString);
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getFirstBooking()->willReturn($firstBooking);
        $booking->getStatus()->willReturn(BookingStatus::CANCELLED);
        $booking->getTransaction()->willReturn($transaction);
        $transaction->setRequest($xml)->shouldBeCalled();
        $transaction->setResponse($responseString)->shouldBeCalled();
        $firstBooking->getProduct()->willReturn($product);
        $product->getIdentifier()->willReturn('identifier');
        $httpClient->call('POST', $xml, [], 'OTA_CancelRQ.aspx')->willReturn($response);
        $logger->addPushBookingSent($pushBooking, Argument::type('string'), $responseString)->shouldBeCalled();

        $this->pushBookingRequest($pushBooking);
    }

    function it_throws_exception_on_wrong_format_response_when_cancel_booking(
        HttpClient $httpClient,
        Environment $templating,
        CmhubLogger $logger,
        PushBooking $pushBooking,
        Booking $booking,
        BookingProduct $firstBooking,
        Product $product,
        ResponseInterface $response,
        StreamInterface $content,
        Transaction $transaction
    )
    {
        $xml = 'xml_data';
        $responseString = 'wrong format response "wrong_format_response"';

        $templating->render(Argument::type('string'), Argument::type('array'))->willReturn($xml);
        $response->getBody()->willReturn($content);
        $response->getStatusCode()->willReturn(500);
        $content->getContents()->willReturn($responseString);
        $httpClient->call('POST', $xml, [], 'OTA_CancelRQ.aspx')->willReturn($response);

        $pushBooking->getBooking()->willReturn($booking);
        $booking->getFirstBooking()->willReturn($firstBooking);
        $booking->getStatus()->willReturn(BookingStatus::CANCELLED);
        $booking->getTransaction()->willReturn($transaction);
        $transaction->setRequest($xml)->shouldBeCalled();
        $transaction->setResponse($responseString)->shouldBeCalled();
        $firstBooking->getProduct()->willReturn($product);
        $product->getIdentifier()->willReturn('identifier');

        $logger->addPushBookingSent($pushBooking, Argument::type('string'), $responseString)->shouldBeCalled();
        $logger->addPushBookingException('wrong format response "' . $responseString . '"', $pushBooking, $this, $xml)->shouldBeCalled();

        $this->shouldThrow(CmhubException::class)->during('pushBookingRequest', [$pushBooking]);
    }

    function it_throws_exception_on_error_and_logs_response_when_cancel_booking(
        HttpClient $httpClient,
        Environment $templating,
        CmhubLogger $logger,
        PushBooking $pushBooking,
        Booking $booking,
        BookingProduct $firstBooking,
        Product $product,
        ResponseInterface $response,
        StreamInterface $content,
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

        $templating->render(Argument::type('string'), Argument::type('array'))->willReturn($xml);
        $response->getBody()->willReturn($content);
        $response->getStatusCode()->willReturn(500);
        $content->getContents()->willReturn($responseString);
        $httpClient->call('POST', $xml, [], 'OTA_CancelRQ.aspx')->willReturn($response);
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getFirstBooking()->willReturn($firstBooking);
        $booking->getStatus()->willReturn(BookingStatus::CANCELLED);
        $booking->getTransaction()->willReturn($transaction);
        $transaction->setRequest($xml)->shouldBeCalled();
        $transaction->setResponse($responseString)->shouldBeCalled();
        $firstBooking->getProduct()->willReturn($product);
        $product->getIdentifier()->willReturn('identifier');
        $logger->addPushBookingSent($pushBooking, Argument::type('string'), $responseString)->shouldBeCalled();
        $logger->addPushBookingException('error message', $pushBooking, $this, $xml)->shouldBeCalled();

        $this->shouldThrow(CmhubException::class)->during('pushBookingRequest', [$pushBooking]);
    }

    function it_render_template_push_booking(
        PushBooking $pushBooking,
        Booking $booking,
        CredentialInterface $smarthotelCredentials,
        Environment $templating,
        GuestInterface $guest
    )
    {
        $dataRender = [
            'apiKey'      => 'username',
            'apiPassword' => 'password',
            'booking'     => $booking,
            'ratePlan'    => RatePlanCode::SBX,
            'customer'    => $guest,
        ];
        $smarthotelCredentials->getUsername()->willReturn('username');
        $smarthotelCredentials->getPassword()->willReturn('password');
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getStatus()->willReturn(BookingStatus::CONFIRMED);
        $booking->getGuests()->willReturn([$guest]);
        $templating->render('Api/Ext/Xml/SmartHotel/V1/PushBooking.xml.twig', $dataRender)->willReturn('template content');

        $this->getRequestBody($booking);
    }

    function it_render_template_cancel_booking(
        PushBooking $pushBooking,
        Booking $booking,
        BookingProduct $bookingProduct,
        CredentialInterface $smarthotelCredentials,
        Environment $templating,
        ProductInterface $product
    )
    {
        $dataRender = [
            'apiKey'      => 'username',
            'apiPassword' => 'password',
            'booking'     => $booking
        ];

        $smarthotelCredentials->getUsername()->willReturn('username');
        $smarthotelCredentials->getPassword()->willReturn('password');
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getStatus()->willReturn(BookingStatus::CANCELLED);
        $booking->getFirstBooking()->willReturn($bookingProduct);
        $bookingProduct->getProduct()->willReturn($product);
        $product->getIdentifier()->willReturn('123456');

        $templating->render('Api/Ext/Xml/SmartHotel/V1/CancelBooking.xml.twig', $dataRender)->willReturn('template content');

        $this->getRequestBody($booking);
    }
}
