<?php

namespace spec\App\Service\ChannelManager\BB8;

use App\Entity\Booking;
use App\Entity\Partner;
use App\Exception\ChannelManagerClientException;
use App\Exception\CmHubException;
use App\Model\BookingCollection;
use App\Entity\Transaction;
use App\Booking\Model\BookingStatus;
use App\Model\Factory\BookingCollectionFactory;
use App\Model\PushBooking;
use App\Service\ChannelManager\BB8\BB8ChannelManager;
use App\Service\ChannelManager\BB8\Serializer\BookingCollectionNormalizer;
use App\Utils\HttpClient;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class BB8ChannelManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BB8ChannelManager::class);
    }

    function let(
        HttpClient $httpClient,
        CmhubLogger $logger,
        BookingCollectionNormalizer $bookingCollectionNormalizer,
        BookingCollectionFactory $bookingCollectionFactory
    )
    {
        $this->beConstructedWith($httpClient, $logger, $bookingCollectionNormalizer, $bookingCollectionFactory);
    }

    function it_throw_cmhub_exception(
        HttpClient $httpClient,
        PushBooking $pushBooking,
        Booking $booking,
        CmhubLogger $logger,
        BookingCollection $bookingCollection,
        BookingCollectionNormalizer $bookingCollectionNormalizer,
        BookingCollectionFactory $bookingCollectionFactory,
        Partner $partner,
        Transaction $transaction
    )
    {
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getPartner()->willReturn($partner);
        $booking->getStatus()->willReturn(BookingStatus::CONFIRMED);
        $bookingCollectionFactory->create()->shouldBeCalled()->willReturn($bookingCollection);
        $bookingCollection->addBooking($booking)->shouldBeCalled()->willReturn($bookingCollection);
        $booking->getTransaction()->willReturn($transaction);
        $bookingCollectionNormalizer->normalize($bookingCollection)->shouldBeCalled()->willReturn($data = ['something']);
        $transaction->setRequest(json_encode($data))->shouldBeCalled()->willReturn($transaction);
        $httpClient->call('POST', $encodedData = json_encode($data), ['Content-Type' => 'application/json'])->shouldBeCalled()->willThrow(new \Exception('exception'));
        $transaction->setResponse('exception')->shouldBeCalled();
        $logger->addPushBookingException('exception', $pushBooking, $this)->shouldBeCalled();

        $this->shouldThrow(CmHubException::class)->during('pushBookingRequest', [$pushBooking]);
    }

    function it_responds_on_success(
        HttpClient $httpClient,
        PushBooking $pushBooking,
        Booking $booking,
        ResponseInterface $response,
        StreamInterface $body,
        CmhubLogger $logger,
        BookingCollection $bookingCollection,
        BookingCollectionNormalizer $bookingCollectionNormalizer,
        BookingCollectionFactory $bookingCollectionFactory,
        Transaction $transaction
    )
    {
        $pushBooking->getBooking()->willReturn($booking);
        $booking->getStatus()->willReturn(BookingStatus::CONFIRMED);
        $bookingCollectionFactory->create()->shouldBeCalled()->willReturn($bookingCollection);
        $bookingCollection->addBooking($booking)->shouldBeCalled()->willReturn($bookingCollection);
        $booking->getTransaction()->willReturn($transaction);
        $bookingCollectionNormalizer->normalize($bookingCollection)->shouldBeCalled()->willReturn($data = ['something']);
        $transaction->setRequest(json_encode($data))->shouldBeCalled()->willReturn($transaction);
        $httpClient->call('POST', $encodedData = json_encode($data), ['Content-Type' => 'application/json'])->shouldBeCalled()->willReturn($response);
        $response->getBody()->willReturn($body);
        $responseData = '{"success":[{"key":"description"}]}';
        $body->getContents()->willReturn($responseData);
        $response->getStatusCode()->willReturn(200);
        $transaction->setResponse($responseData)->shouldBeCalled()->willReturn($transaction);
        $transaction->setStatusCode('200')->shouldBeCalled()->willReturn($transaction);
        $logger->addPushBookingSent($pushBooking, $encodedData, $responseData)->shouldBeCalled();

        $this->pushBookingRequest($pushBooking);
    }
}
