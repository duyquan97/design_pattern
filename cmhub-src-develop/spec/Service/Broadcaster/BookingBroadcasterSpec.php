<?php

namespace spec\App\Service\Broadcaster;

use App\Entity\Booking;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Repository\BookingRepository;
use App\Exception\ChannelManagerNotSupportedException;
use App\Exception\CmHubException;
use App\Exception\MissingTransactionDataException;
use App\Model\Factory\PushBookingFactory;
use App\Model\PushBooking;
use App\Service\Broadcaster\BookingBroadcaster;
use App\Service\ChannelManager\ChannelManagerInterface;
use App\Service\ChannelManager\ChannelManagerResolver;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;

/**
 * Class IresaPushBookingsBroadcasterSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingBroadcasterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingBroadcaster::class);
    }

    function let(
        BookingRepository $bookingRepository,
        PushBookingFactory $pushBookingFactory,
        ChannelManagerResolver $channelManagerResolver
    ) {
        $this->beConstructedWith($pushBookingFactory, $bookingRepository, $channelManagerResolver);
    }

    function it_support_booking_type(Transaction $transaction)
    {
        $transaction->getType()->shouldBeCalled()->willReturn(TransactionType::BOOKING);

        $this->support($transaction)->shouldBe(true);
    }

    /**
     * @param Transaction|Collaborator            $transaction
     * @param PushBookingFactory|Collaborator     $pushBookingFactory
     * @param PushBooking|Collaborator            $pushBooking
     * @param BookingRepository                   $bookingRepository
     * @param Booking|Collaborator                $booking
     * @param Partner|Collaborator                $partner
     * @param EntityManagerInterface              $entityManager
     * @param ChannelManager|Collaborator         $channelManager
     * @param ChannelManagerResolver|Collaborator $channelManagerResolver
     * @param ChannelManagerInterface             $channelManagerIntegration
     *
     * @throws ChannelManagerNotSupportedException
     * @throws MissingTransactionDataException
     * @throws CmHubException
     */
    function it_broadcast(
        BookingRepository $bookingRepository,
        Transaction $transaction,
        PushBookingFactory $pushBookingFactory,
        PushBooking $pushBooking,
        Booking $booking,
        Partner $partner,
        EntityManagerInterface $entityManager,
        ChannelManager $channelManager,
        ChannelManagerResolver $channelManagerResolver,
        ChannelManagerInterface $channelManagerIntegration
    ) {
        $channelManager->isPushBookings()->shouldBeCalled()->willReturn(true);
        $entityManager->getRepository(Booking::class)->willReturn($bookingRepository);
        $bookingRepository->findOneBy(['transaction' => $transaction])->shouldBeCalled()->willReturn($booking);

        $channelManagerResolver->getIntegration($channelManager)->willReturn($channelManagerIntegration);
        $channelManagerIntegration->pushBookingRequest($pushBooking)->shouldBeCalled();


        $partner->getChannelManager()->shouldBeCalled()->willReturn($channelManager);
        $booking->getPartner()->shouldBeCalled()->willReturn($partner);
        $pushBooking->setBooking($booking)->shouldBeCalled()->willReturn($pushBooking);
        $pushBookingFactory->create()->shouldBeCalled()->willReturn($pushBooking);

        $this->broadcast($transaction);
    }
}
