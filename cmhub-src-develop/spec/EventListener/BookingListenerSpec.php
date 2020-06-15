<?php

namespace spec\App\EventListener;

use App\Entity\Booking;
use App\Entity\Partner;
use App\EventListener\BookingListener;
use App\Message\BookingCreated;
use App\Message\Factory\BookingCreatedFactory;
use App\Message\Factory\SendBookingToChannelFactory;
use App\Message\SendBookingToChannel;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class BookingListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingListener::class);
    }

    function let(
        MessageBusInterface $messageBus, BookingCreatedFactory $bookingCreatedFactory, SendBookingToChannelFactory $sendBookingToChannelFactory
    )
    {
        $this->beConstructedWith($messageBus, $bookingCreatedFactory, $sendBookingToChannelFactory);
    }

    function it_create_new_booking(Booking $booking, LifecycleEventArgs $eventArgs, MessageBusInterface $messageBus, BookingCreatedFactory $bookingCreatedFactory, BookingCreated $message)
    {
        $eventArgs->getEntity()->willReturn($booking);
        $booking->getIdentifier()->willReturn('id');
        $bookingCreatedFactory->create('id')->willReturn($message);
        $messageBus->dispatch($message)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));

        $this->postPersist($eventArgs);
    }

    function it_update_existing_booking(Booking $booking, LifecycleEventArgs $eventArgs, PreUpdateEventArgs $preUpdate, MessageBusInterface $messageBus, SendBookingToChannelFactory $sendBookingToChannelFactory, SendBookingToChannel $message)
    {
        $eventArgs->getEntity()->willReturn($booking);
        $booking->getIdentifier()->willReturn('id');
        $booking->setTransaction(null)->shouldBeCalled();
        $sendBookingToChannelFactory->create('id')->willReturn($message);
        $messageBus->dispatch($message)->shouldBeCalled()->willReturn(new Envelope(new \stdClass()));
        $preUpdate->getEntity()->willReturn($booking);
        $preUpdate->hasChangedField('status')->willReturn(true);
        $this->preUpdate($preUpdate);

        $this->postUpdate($eventArgs);
    }

    function it_only_listens_booking_entity_update(Partner $partner, PreUpdateEventArgs $eventArgs, SendBookingToChannelFactory $sendBookingToChannelFactory)
    {
        $eventArgs->getEntity()->willReturn($partner);
        $sendBookingToChannelFactory->create(Argument::type('string'))->shouldNotBeCalled();

        $this->postUpdate($eventArgs);
    }

    function it_only_listens_booking_entity_created(Partner $partner, PreUpdateEventArgs $eventArgs, BookingCreatedFactory $bookingCreatedFactory)
    {
        $eventArgs->getEntity()->willReturn($partner);
        $bookingCreatedFactory->create(Argument::type('string'))->shouldNotBeCalled();

        $this->postPersist($eventArgs);
    }
}
