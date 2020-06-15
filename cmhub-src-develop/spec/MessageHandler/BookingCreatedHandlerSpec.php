<?php

namespace spec\App\MessageHandler;

use App\Entity\Booking;
use App\Entity\BookingProduct;
use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Entity\Product;
use App\Message\Factory\SendBookingToChannelFactory;
use App\Message\BookingCreated;
use App\Message\SendBookingToChannel;
use App\MessageHandler\BookingCreatedHandler;
use App\Repository\BookingRepository;
use App\Booking\BookingProcessorManager;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

class BookingCreatedHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingCreatedHandler::class);
    }

    function let(
        BookingRepository $bookingRepository,
        MessageBusInterface $messageBus,
        SendBookingToChannelFactory $messageFactory,
        Partner $partner,
        ChannelManager $channelManager,
        Booking $booking,
        BookingCreated $processNewBookingMessage
    )
    {
        $processNewBookingMessage->getIdentifier()->willReturn('pepito');
        $bookingRepository->findOneBy(['identifier' => 'pepito'])->willReturn($booking);
        $booking->getReservationId()->willReturn('pepito');
        $booking->getPartner()->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);

        $this->beConstructedWith($bookingRepository, $messageBus, $messageFactory);
    }

    function it_handles_process_new_booking_message(
        MessageBusInterface $messageBus,
        SendBookingToChannelFactory $messageFactory,
        BookingCreated $processNewBookingMessage,
        SendBookingToChannel $sendBookingToChannelMessage,
        Booking $booking1,
        ChannelManager $channelManager,
        Partner $partner
    )
    {
        $envelope = new Envelope($sendBookingToChannelMessage);
        $booking1->getPartner()->willReturn($partner);
        $partner->isEnabled()->willReturn(true);
        $channelManager->isPushBookings()->willReturn(true);
        $messageFactory->create('pepito')->willReturn($sendBookingToChannelMessage);
        $messageBus->dispatch($sendBookingToChannelMessage, Argument::type('array'))->shouldBeCalled()->willReturn($envelope);
        $this->__invoke($processNewBookingMessage);
    }

    function it_does_not_dispatch_send_booking_message_if_cm_is_not_push_bookings(
        MessageBusInterface $messageBus,
        BookingCreated $processNewBookingMessage,
        SendBookingToChannel $sendBookingToChannelMessage,
        Booking $booking1,
        ChannelManager $channelManager,
        Partner $partner
    )
    {
        $booking1->getPartner()->willReturn($partner);
        $partner->isEnabled()->willReturn(true);
        $channelManager->isPushBookings()->willReturn(false);
        $messageBus->dispatch($sendBookingToChannelMessage, Argument::type('array'))->shouldNotBeCalled();
        $this->__invoke($processNewBookingMessage);
    }

    function it_does_not_dispatch_send_booking_message_if_partner_disabled(
        MessageBusInterface $messageBus,
        BookingCreated $processNewBookingMessage,
        SendBookingToChannel $sendBookingToChannelMessage,
        Booking $booking1,
        ChannelManager $channelManager,
        Partner $partner
    )
    {
        $booking1->getPartner()->willReturn($partner);
        $partner->isEnabled()->willReturn(false);
        $channelManager->isPushBookings()->willReturn(true);
        $messageBus->dispatch($sendBookingToChannelMessage, Argument::type('array'))->shouldNotBeCalled();

        $this->__invoke($processNewBookingMessage);
    }

    function it_throws_unrecoverable_exception_if_booking_not_found(
        BookingRepository $bookingRepository,
        BookingCreated $processNewBookingMessage
    )
    {
        $bookingRepository->findOneBy(['identifier' => 'pepito'])->willReturn();
        $this->shouldThrow(UnrecoverableMessageHandlingException::class)->during('__invoke', [$processNewBookingMessage]);
    }
}
