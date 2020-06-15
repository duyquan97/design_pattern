<?php

namespace spec\App\Service;

use App\Entity\Booking;
use App\Entity\ChannelManager;
use App\Entity\Factory\TransactionFactory;
use App\Entity\Partner;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Message\Factory\TransactionScheduledFactory;
use App\Message\TransactionScheduled;
use App\Model\BookingCollection;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductInterface;
use App\Model\ProductRateCollection;
use App\Model\ProductRateCollectionInterface;
use App\Service\BookingEngineInterface;
use App\Service\BookingEngineManager;
use App\Service\Chaining\ChainingHelper;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class BookingEngineManagerSpec extends ObjectBehavior
{
    /**
     * @var BookingEngineInterface[]
     */
    private $bookingEngines;

    function it_is_initializable()
    {
        $this->shouldHaveType(BookingEngineManager::class);
    }

    function let(BookingEngineInterface $bookingEngine, BookingEngineInterface $bookingEngine1, ChainingHelper $chainingHelper,
                 TransactionFactory $transactionFactory, EntityManagerInterface $entityManager)
    {
        $this->bookingEngines = [
            $bookingEngine,
            $bookingEngine1
        ];
        $this->beConstructedWith($chainingHelper, $transactionFactory, $entityManager);
        $this->addBookingEngine($bookingEngine);
        $this->addBookingEngine($bookingEngine1);
    }

    function it_doesnt_update_availability_if_collection_is_empty(ProductAvailabilityCollectionInterface $productAvailabilities)
    {
        $productAvailabilities->isEmpty()->willReturn(true);
        $this->updateAvailability($productAvailabilities)->shouldBe($productAvailabilities);
    }

    function it_updates_availability(ProductAvailabilityCollectionInterface $productAvailabilities, BookingEngineInterface $bookingEngine,
         BookingEngineInterface $bookingEngine1, ChainingHelper $chainingHelper)
    {
        $productAvailabilities->isEmpty()->willReturn(false);
        $chainingHelper->chainAvailabilities($productAvailabilities)->shouldBeCalled()->willReturn($productAvailabilities);
        $bookingEngine->updateAvailability($productAvailabilities)->willReturn($productAvailabilities);
        $bookingEngine1->updateAvailability($productAvailabilities)->willReturn($productAvailabilities);

        $this->updateAvailability($productAvailabilities)->shouldBe($productAvailabilities);
    }

    function it_gets_availabilities(PartnerInterface $partner, BookingEngineInterface $bookingEngine, ProductAvailabilityCollectionInterface $productAvailabilityCollection, ProductInterface $product, ProductInterface $product1)
    {
        $bookingEngine->getAvailabilities($partner, (new \DateTime('2018-04-20')), (new \DateTime('2018-04-21')), [
            $product,
            $product1
        ])->willReturn($productAvailabilityCollection);

        $this->getAvailabilities($partner, (new \DateTime('2018-04-20')), (new \DateTime('2018-04-21')), [
            $product,
            $product1
        ])->shouldBe($productAvailabilityCollection);
    }

    function it_gets_bookings_not_yet_have_transaction(
        BookingEngineInterface $bookingEngine,
        BookingCollection $bookingCollection,
        Partner $partner,
        EntityManagerInterface $entityManager,
        TransactionFactory $transactionFactory,
        ChannelManager $channelManager,
        Transaction $transaction,
        Booking $booking
    )
    {
        $bookingEngine->getBookings((new \DateTime('2018-04-20')), (new \DateTime('2018-04-21')), null, [$partner], null)->willReturn($bookingCollection);

        $bookingCollection->getBookings()->willReturn([$booking]);
        $booking->getTransaction()->shouldBeCalled()->willReturn(null);
        $booking->getPartner()->willReturn($partner);

        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('channel_name');

        $transactionFactory->create(TransactionType::BOOKING, 'channel_name', TransactionStatus::SUCCESS, $partner)->willReturn($transaction);
        $transaction->setStatusCode(200)->shouldBeCalled()->willReturn($transaction);
        $transaction->setSentAt(
            Argument::that(function (\DateTime $start) {
                return $start->format('Y-m-d') === (new \DateTime())->format('Y-m-d');
            })
        )->shouldBeCalled()->willReturn($transaction);

        $booking->setTransaction($transaction)->shouldBeCalled();

        $entityManager->persist($booking)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalledOnce();

        $this->getBookings((new \DateTime('2018-04-20')), (new \DateTime('2018-04-21')), null, [$partner])->shouldBe($bookingCollection);
    }

    function it_gets_bookings_exists_transaction(
        BookingEngineInterface $bookingEngine,
        BookingCollection $bookingCollection,
        Partner $partner,
        EntityManagerInterface $entityManager,
        TransactionFactory $transactionFactory,
        ChannelManager $channelManager,
        Transaction $transaction,
        Booking $booking
    )
    {
        $bookingEngine->getBookings((new \DateTime('2018-04-20')), (new \DateTime('2018-04-21')), null, [$partner], null)->willReturn($bookingCollection);

        $bookingCollection->getBookings()->willReturn([$booking]);
        $booking->getPartner()->willReturn($partner);
        $booking->getTransaction()->shouldBeCalled();

        $partner->getChannelManager()->shouldNotBeCalled();
        $channelManager->getIdentifier()->shouldNotBeCalled();

        $transactionFactory->create(TransactionType::BOOKING, 'channel_name', TransactionStatus::SUCCESS, $partner)->shouldNotBeCalled();
        $transaction->setStatusCode(200)->shouldNotBeCalled();
        $transaction->setSentAt(
            Argument::that(function (\DateTime $start) {
                return $start->format('Y-m-d') === (new \DateTime())->format('Y-m-d');
            })
        )->shouldNotBeCalled();

        $booking->setTransaction($transaction)->shouldNotBeCalled();

        $entityManager->persist($booking)->shouldNotBeCalled();
        $entityManager->flush()->shouldBeCalledOnce();

        $this->getBookings((new \DateTime('2018-04-20')), (new \DateTime('2018-04-21')), null, [$partner])->shouldBe($bookingCollection);
    }

    function it_gets_bookings_all_partners(
        BookingEngineInterface $bookingEngine,
        BookingCollection $bookingCollection,
        ChannelManager $channelManager,
        Booking $booking,
        Transaction $transaction,
        TransactionFactory $transactionFactory,
        EntityManagerInterface $entityManager,
        Partner $partner
    )
    {
        $bookingEngine->getBookings((new \DateTime('2018-04-20')), (new \DateTime('2018-04-21')), $channelManager, [], null)->willReturn($bookingCollection);
        $bookingCollection->getBookings()->willReturn([$booking]);
        $booking->getTransaction()->shouldBeCalled()->willReturn(null);
        $booking->getPartner()->willReturn($partner);

        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('channel_name');

        $transactionFactory->create(TransactionType::BOOKING, 'channel_name', TransactionStatus::SUCCESS, $partner)->willReturn($transaction);
        $transaction->setStatusCode(200)->shouldBeCalled()->willReturn($transaction);
        $transaction->setSentAt(
            Argument::that(function (\DateTime $start) {
                return $start->format('Y-m-d') === (new \DateTime())->format('Y-m-d');
            })
        )->shouldBeCalled()->willReturn($transaction);

        $booking->setTransaction($transaction)->shouldBeCalled();

        $entityManager->persist($booking)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalledOnce();
        $this->getBookings((new \DateTime('2018-04-20')), (new \DateTime('2018-04-21')), $channelManager, [])->shouldBe($bookingCollection);
    }

    function it_doesnt_update_rates_if_collection_is_empty(ProductRateCollectionInterface $productRateCollection, TransactionFactory $transactionFactory)
    {
        $productRateCollection->isEmpty()->willReturn(true);
        $transactionFactory->create(Argument::cetera())->shouldNotBeCalled();
        $this->updateRates($productRateCollection)->shouldBe($productRateCollection);
    }

    function it_updates_rates(ProductRateCollectionInterface $productRateCollection, BookingEngineInterface $bookingEngine,
                              BookingEngineInterface $bookingEngine1, ChainingHelper $chainingHelper, Partner $partner)
    {
        $productRateCollection->isEmpty()->willReturn(false);
        $productRateCollection->getPartner()->willReturn($partner);

        $chainingHelper->chainRates($productRateCollection)->shouldBeCalled()->willReturn($productRateCollection);
        $bookingEngine->updateRates($productRateCollection)->willReturn($productRateCollection);
        $bookingEngine1->updateRates($productRateCollection)->willReturn($productRateCollection);
        $this->updateRates($productRateCollection)->shouldBe($productRateCollection);
    }

    function it_gets_rates(PartnerInterface $partner, BookingEngineInterface $bookingEngine, ProductRateCollection $productRateCollection, ProductInterface $product, ProductInterface $product1)
    {
        $bookingEngine->getRates($partner, (new \DateTime('2018-04-20')), (new \DateTime('2018-04-21')), [
            $product,
            $product1
        ])->willReturn($productRateCollection);

        $this->getRates($partner, (new \DateTime('2018-04-20')), (new \DateTime('2018-04-21')), [
            $product,
            $product1
        ])->shouldBe($productRateCollection);
    }
}
