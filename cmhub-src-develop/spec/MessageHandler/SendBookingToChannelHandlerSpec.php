<?php

namespace spec\App\MessageHandler;

use App\Entity\Booking;
use App\Entity\ChannelManager;
use App\Entity\Factory\TransactionFactory;
use App\Entity\Partner;
use App\Entity\Transaction;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Exception\TransactionFailedException;
use App\Message\SendBookingToChannel;
use App\MessageHandler\SendBookingToChannelHandler;
use App\Repository\BookingRepository;
use App\Service\Broadcaster\BroadcastManager;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class SendBookingToChannelHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SendBookingToChannelHandler::class);
    }

    function let(
        BroadcastManager $broadcastManager,
        TransactionFactory $transactionFactory,
        BookingRepository $bookingRepository,
        EntityManagerInterface $entityManager,
        SendBookingToChannel $sendBookingToChannelMessage,
        Booking $booking,
        ChannelManager $channelManager,
        Partner $partner
    )
    {
        $sendBookingToChannelMessage->getIdentifier()->willReturn('pepito');
        $bookingRepository->findOneBy(['identifier' => 'pepito'])->willReturn($booking);
        $booking->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('pepito');
        $booking->getPartner()->willReturn($partner);
        $this->beConstructedWith($broadcastManager, $transactionFactory, $bookingRepository, $entityManager);
    }

    function it_handles_send_booking_to_channel_message_creating_transaction(
        BroadcastManager $broadcastManager,
        TransactionFactory $transactionFactory,
        EntityManagerInterface $entityManager,
        SendBookingToChannel $sendBookingToChannelMessage,
        Booking $booking,
        Partner $partner,
        Transaction $transaction
    )
    {
        $booking->getTransaction()->willReturn();
        $transactionFactory
            ->create(
                TransactionType::BOOKING,
                'pepito',
                TransactionStatus::SCHEDULED,
                $partner
            )
            ->willReturn($transaction);

        $booking->setTransaction($transaction)->shouldBeCalled()->willReturn($booking);
        $entityManager->persist($booking)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $broadcastManager->broadcast($transaction)->shouldBeCalled()->willReturn($transaction);
        $transaction->isFailed()->willReturn(false);
        $this->__invoke($sendBookingToChannelMessage);
    }

    function it_handles_send_booking_to_channel_message_existing_transaction(
        BroadcastManager $broadcastManager,
        TransactionFactory $transactionFactory,
        EntityManagerInterface $entityManager,
        SendBookingToChannel $sendBookingToChannelMessage,
        Booking $booking,
        Transaction $transaction
    ) {
        $booking->getTransaction()->willReturn($transaction);
        $transactionFactory->create(Argument::cetera())->shouldNotBeCalled();
        $entityManager->persist($booking)->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();
        $broadcastManager->broadcast($transaction)->shouldBeCalled()->willReturn($transaction);
        $transaction->isFailed()->willReturn(false);
        $this->__invoke($sendBookingToChannelMessage);
    }

    function it_throws_transaction_failed_exception_if_transaction_is_failed(
        BroadcastManager $broadcastManager,
        TransactionFactory $transactionFactory,
        EntityManagerInterface $entityManager,
        SendBookingToChannel $sendBookingToChannelMessage,
        Booking $booking,
        Partner $partner,
        Transaction $transaction
    ) {
        $booking->getTransaction()->willReturn();
        $transactionFactory
            ->create(
                TransactionType::BOOKING,
                'pepito',
                TransactionStatus::SCHEDULED,
                $partner
            )
            ->willReturn($transaction);

        $booking->setTransaction($transaction)->shouldBeCalled()->willReturn($booking);
        $entityManager->persist($booking)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $broadcastManager->broadcast($transaction)->shouldBeCalled()->willReturn($transaction);
        $transaction->isFailed()->willReturn(true);
        $transaction->getResponse()->willReturn('whatever');
        $this->shouldThrow(TransactionFailedException::class)->during('__invoke', [$sendBookingToChannelMessage]);
    }

    function it_throws_unrecoverable_exception_if_booking_not_present_in_db(
        BookingRepository $bookingRepository,
        SendBookingToChannel $sendBookingToChannelMessage
    ) {
        $bookingRepository->findOneBy(['identifier' => 'pepito'])->willReturn();
        $this->shouldThrow(UnrecoverableMessageHandlingException::class)->during('__invoke', [$sendBookingToChannelMessage]);
    }
}
