<?php

namespace App\EventListener;

use App\Entity\Booking;
use App\Message\Factory\BookingCreatedFactory;
use App\Message\Factory\SendBookingToChannelFactory;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class BookingListener
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingListener
{
    /**
     * @var bool
     */
    private $statusChanged = false;

    /**
     *
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var BookingCreatedFactory
     */
    private $bookingCreatedFactory;

    /**
     * @var SendBookingToChannelFactory
     */
    private $sendBookingToChannelFactory;

    /**
     * BookingUpdatedListener constructor.
     *
     * @param MessageBusInterface $messageBus
     * @param BookingCreatedFactory $bookingCreatedFactory
     * @param SendBookingToChannelFactory $sendBookingToChannelFactory
     */
    public function __construct(MessageBusInterface $messageBus, BookingCreatedFactory $bookingCreatedFactory, SendBookingToChannelFactory $sendBookingToChannelFactory)
    {
        $this->messageBus = $messageBus;
        $this->bookingCreatedFactory = $bookingCreatedFactory;
        $this->sendBookingToChannelFactory = $sendBookingToChannelFactory;
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     *
     * @return void
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $booking = $eventArgs->getEntity();

        if (!$booking instanceof Booking) {
            return;
        }

        $this->messageBus->dispatch($this->bookingCreatedFactory->create($booking->getIdentifier()));
    }

    /**
     * @param PreUpdateEventArgs $eventArgs
     *
     * @return void
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $booking = $eventArgs->getEntity();

        if (!$booking instanceof Booking) {
            return;
        }

        if (!$eventArgs->hasChangedField('status')) {
            return;
        }

        $this->statusChanged = true;
        $booking->setTransaction(null);
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     *
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $booking = $eventArgs->getEntity();

        if (!$booking instanceof Booking) {
            return;
        }

        if (!$this->statusChanged) {
            return;
        }

        $this->messageBus->dispatch($this->sendBookingToChannelFactory->create($booking->getIdentifier()));
        $this->statusChanged = false;
    }
}
