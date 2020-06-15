<?php

namespace spec\App\Service\HubEngine;

use App\Entity\Availability as AvailabilityEntity;
use App\Entity\Booking;
use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\TransactionChannel;
use App\Message\Factory\AvailabilityUpdatedFactory;
use App\Message\Factory\RateUpdatedFactory;
use App\Message\AvailabilityUpdated;
use App\Model\Availability;
use App\Model\AvailabilitySource;
use App\Model\BookingCollection;
use App\Model\Factory\BookingCollectionFactory;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductRate;
use App\Entity\ProductRate as RateEntity;
use App\Model\ProductRateCollection;
use App\Model\Rate;
use App\Repository\AvailabilityRepository;
use App\Repository\BookingRepository;
use App\Repository\ProductRateRepository;
use App\Service\HubEngine\CmHubBookingEngine;
use App\Service\Loader\ProductLoader;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class CmHubBookingEngineSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CmHubBookingEngine::class);
    }

    function let(EntityManagerInterface $entityManager, ProductLoader $productLoader, MessageBusInterface $messageBus, AvailabilityUpdatedFactory $availabilityMessageFactory, RateUpdatedFactory $priceMessageFactory, ProductAvailabilityCollectionFactory $availabilityCollectionFactory, ProductRateCollectionFactory $productRateCollectionFactory, BookingCollectionFactory $bookingCollectionFactory)
    {
        $this->beConstructedWith($entityManager, $productLoader, $messageBus, $availabilityMessageFactory, $priceMessageFactory, $availabilityCollectionFactory, $productRateCollectionFactory, $bookingCollectionFactory);
    }

    function it_update_availability(ProductAvailabilityCollection $productAvailabilities, ProductAvailabilityCollectionFactory $availabilityCollectionFactory, Partner $partner, ProductAvailability $productAvailability,
                                    Availability $availability1, Availability $availability2, AvailabilityRepository $availabilityRepository, EntityManagerInterface $entityManager, ProductAvailabilityCollection $collection,
                                    Product $product, AvailabilityEntity $availability3, AvailabilityEntity $availability4, MessageBusInterface $messageBus, AvailabilityUpdatedFactory $availabilityMessageFactory, AvailabilityUpdated $message)
    {
        $day1 = new \DateTime('2019-05-01');
        $day2 = new \DateTime('2019-05-02');
        $entityManager->getRepository(AvailabilityEntity::class)->willReturn($availabilityRepository);
        $productAvailabilities->getPartner()->willReturn($partner);
        $partner->getConnectedAt()->willReturn(null);
        $productAvailabilities->getProductAvailabilities()->willReturn([$productAvailability]);
        $productAvailabilities->getSource()->willReturn(AvailabilitySource::CM);
        $productAvailability->getAvailabilities()->willReturn([$availability1, $availability2]);
        $productAvailability->getProduct()->willReturn($product);
        $productAvailabilities->getTransaction()->willReturn(null);
        $availability1->getStart()->willReturn($day1);
        $availability1->getEnd()->willReturn($day1);
        $availability1->getStock()->willReturn(1);
        $availability1->isStopSale()->willReturn(false);
        $availability1->getProduct()->shouldBeCalled()->willReturn($product);
        $availability2->getStart()->willReturn($day2);
        $availability2->getEnd()->willReturn($day2);
        $availability2->getStock()->willReturn(2);
        $availability2->isStopSale()->willReturn(false);
        $availability2->getProduct()->shouldBeCalled()->willReturn($product);

        $availabilityRepository->findOneBy(
            [
                'date' => $day1,
                'product' => $product,
            ]
        )->willReturn($availability3);
        $availabilityRepository->findOneBy(
            [
                'date' => $day2,
                'product' => $product,
            ]
        )->willReturn($availability4);
        $availability3->setProduct($product)->willReturn($availability3);
        $availability4->setProduct($product)->willReturn($availability4);
        $availability3->setPartner($partner)->willReturn($availability3);
        $availability4->setPartner($partner)->willReturn($availability4);
        $availability3->setDate(Argument::type('DateTime'))->willReturn($availability3);
        $availability4->setDate(Argument::type('DateTime'))->willReturn($availability4);
        $availability3->setTransaction(null)->willReturn($availability3);
        $availability4->setTransaction(null)->willReturn($availability4);
        $availability3->setStock(1)->willReturn($availability3);
        $availability4->setStock(2)->willReturn($availability4);
        $availability3->setStopSale(false)->willReturn($availability3);
        $availability4->setStopSale(false)->willReturn($availability4);
        $availability3->getId()->willReturn(3);
        $availability4->getId()->willReturn(4);
        $availabilityCollectionFactory->create($partner)->willReturn($collection);
        $collection->setSource(AvailabilitySource::CM)->willReturn($collection);
        $collection->getPartner()->willReturn($partner);
        $partner->getConnectedAt()->willReturn(null);
        $partner->setConnectedAt(Argument::type('DateTime'))->shouldBeCalled()->willReturn($partner);
        $collection->addAvailability($availability3)->shouldBeCalled()->willReturn($collection);
        $collection->addAvailability($availability4)->shouldBeCalled()->willReturn($collection);
        $entityManager->persist($partner)->shouldBeCalled();
        $entityManager->persist($availability3)->shouldBeCalled();
        $entityManager->persist($availability4)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $productAvailabilities->getChannel()->willReturn('eai');
        $availabilityMessageFactory->create(Argument::type('array'), 'eai')->willReturn($message);
        $messageBus->dispatch($message)->shouldBeCalled()->willReturn(new Envelope(new \StdClass()));

        $this->updateAvailability($productAvailabilities)->shouldBe($collection);
    }

    function it_align_availability_with_iresa(ProductAvailabilityCollection $productAvailabilities, ProductAvailabilityCollectionFactory $availabilityCollectionFactory, Partner $partner, ProductAvailability $productAvailability,
                                    Availability $availability1, Availability $availability2, AvailabilityRepository $availabilityRepository, EntityManagerInterface $entityManager, ProductAvailabilityCollection $collection,
                                    Product $product, AvailabilityEntity $availability3, AvailabilityEntity $availability4, MessageBusInterface $messageBus, AvailabilityUpdatedFactory $availabilityMessageFactory, AvailabilityUpdated $message)
    {
        $day1 = new \DateTime('2019-05-01');
        $day2 = new \DateTime('2019-05-02');
        $entityManager->getRepository(AvailabilityEntity::class)->willReturn($availabilityRepository);
        $productAvailabilities->getPartner()->willReturn($partner);
        $partner->getConnectedAt()->willReturn(null);
        $productAvailabilities->getProductAvailabilities()->willReturn([$productAvailability]);
        $productAvailabilities->getSource()->willReturn(AvailabilitySource::ALIGNMENT);
        $productAvailability->getAvailabilities()->willReturn([$availability1, $availability2]);
        $productAvailability->getProduct()->willReturn($product);
        $productAvailabilities->getTransaction()->willReturn(null);
        $availability1->getStart()->willReturn($day1);
        $availability1->getEnd()->willReturn($day1);
        $availability1->getStock()->willReturn(1);
        $availability1->isStopSale()->willReturn(false);
        $availability1->getProduct()->shouldBeCalled()->willReturn($product);
        $availability2->getStart()->willReturn($day2);
        $availability2->getEnd()->willReturn($day2);
        $availability2->getStock()->willReturn(2);
        $availability2->isStopSale()->willReturn(false);
        $availability2->getProduct()->shouldBeCalled()->willReturn($product);

        $availabilityRepository->findOneBy(
            [
                'date' => $day1,
                'product' => $product,
            ]
        )->willReturn($availability3);
        $availabilityRepository->findOneBy(
            [
                'date' => $day2,
                'product' => $product,
            ]
        )->willReturn($availability4);
        $availability3->setProduct($product)->willReturn($availability3);
        $availability4->setProduct($product)->willReturn($availability4);
        $availability3->setPartner($partner)->willReturn($availability3);
        $availability4->setPartner($partner)->willReturn($availability4);
        $availability3->setDate(Argument::type('DateTime'))->willReturn($availability3);
        $availability4->setDate(Argument::type('DateTime'))->willReturn($availability4);
        $availability3->setTransaction(null)->willReturn($availability3);
        $availability4->setTransaction(null)->willReturn($availability4);
        $availability3->setStock(1)->willReturn($availability3);
        $availability4->setStock(2)->willReturn($availability4);
        $availability3->setStopSale(false)->willReturn($availability3);
        $availability4->setStopSale(false)->willReturn($availability4);
        $availability3->getId()->willReturn(3);
        $availability4->getId()->willReturn(4);
        $availabilityCollectionFactory->create($partner)->willReturn($collection);
        $collection->setSource(AvailabilitySource::ALIGNMENT)->willReturn($collection);
        $collection->getPartner()->willReturn($partner);
        $partner->getConnectedAt()->willReturn(null);
        $partner->setConnectedAt(Argument::type('DateTime'))->shouldNotBeCalled();
        $collection->addAvailability($availability3)->shouldBeCalled()->willReturn($collection);
        $collection->addAvailability($availability4)->shouldBeCalled()->willReturn($collection);
        $entityManager->persist($partner)->shouldNotBeCalled();
        $entityManager->persist($availability3)->shouldBeCalled();
        $entityManager->persist($availability4)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $productAvailabilities->getChannel()->willReturn('eai');
        $availabilityMessageFactory->create(Argument::type('array'), 'eai')->willReturn($message);
        $messageBus->dispatch($message)->shouldBeCalled()->willReturn(new Envelope(new \StdClass()));

        $this->updateAvailability($productAvailabilities)->shouldBe($collection);
    }

    function it_update_rate(ProductRateCollection $productRates, Partner $partner, ProductRate $productRate, Rate $rate1, Rate $rate2, RateEntity $rate3, RateEntity $rate4,
                            ProductRateRepository $productRateRepository, EntityManagerInterface $entityManager, Product $product,
                            MessageBusInterface $messageBus, RateUpdatedFactory $priceMessageFactory, AvailabilityUpdated $message)
    {
        $day1 = new \DateTime('2019-05-01');
        $day2 = new \DateTime('2019-05-02');
        $entityManager->getRepository(RateEntity::class)->willReturn($productRateRepository);
        $productRates->getPartner()->willReturn($partner);
        $partner->getConnectedAt()->willReturn(null);
        $productRates->getProductRates()->willReturn([$productRate]);
        $productRate->getRates()->willReturn([$rate1, $rate2]);
        $productRate->getProduct()->willReturn($product);
        $productRates->getTransaction()->willReturn(null);
        $productRates->getChannel()->willReturn(TransactionChannel::IRESA);

        $rate1->getStart()->willReturn($day1);
        $rate1->getEnd()->willReturn($day1);
        $rate1->getAmount()->willReturn(1);
        $rate2->getStart()->willReturn($day2);
        $rate2->getEnd()->willReturn($day2);
        $rate2->getAmount()->willReturn(2);

        $productRateRepository->findOneBy(
            [
                'partner' => $partner,
                'date' => $day1,
                'product' => $product,
            ]
        )->willReturn($rate3);
        $productRateRepository->findOneBy(
            [
                'partner' => $partner,
                'date' => $day2,
                'product' => $product,
            ]
        )->willReturn($rate4);

        $rate3->setProduct($product)->willReturn($rate3);
        $rate4->setProduct($product)->willReturn($rate4);
        $rate3->setPartner($partner)->willReturn($rate3);
        $rate4->setPartner($partner)->willReturn($rate4);
        $rate3->setDate(Argument::type('DateTime'))->willReturn($rate3);
        $rate4->setDate(Argument::type('DateTime'))->willReturn($rate4);
        $rate3->setTransaction(null)->willReturn($rate3);
        $rate4->setTransaction(null)->willReturn($rate4);
        $rate3->setAmount(1)->willReturn($rate3);
        $rate4->setAmount(2)->willReturn($rate4);
        $rate3->getId()->willReturn(3);
        $rate4->getId()->willReturn(4);

        $entityManager->persist($rate3)->shouldBeCalled();
        $entityManager->persist($rate4)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $priceMessageFactory->create(Argument::type('array'), TransactionChannel::IRESA)->willReturn($message);
        $messageBus->dispatch($message)->shouldBeCalled()->willReturn(new Envelope(new \StdClass()));

        $this->updateRates($productRates)->shouldBe($productRates);
    }

    function it_get_availabilities(Partner $partner, Product $product1, Product $product2, EntityManagerInterface $entityManager,
       AvailabilityRepository $availabilityRepository, AvailabilityEntity $availability1, AvailabilityEntity $availability2,
       AvailabilityEntity $availability3, AvailabilityEntity $availability4, ProductAvailabilityCollectionFactory $availabilityCollectionFactory, ProductAvailabilityCollection $collection)
    {
        $availabilityCollectionFactory->create($partner)->willReturn($collection);
        $entityManager->getRepository(AvailabilityEntity::class)->willReturn($availabilityRepository);
        $start = new \DateTime('2019-01-10');
        $end = new \DateTime('2019-01-11');
        $availabilityRepository->findByDateRange($partner, $start, $end, [$product1, $product2])->willReturn([$availability1, $availability2, $availability3, $availability4]);
        $availability1->getProduct()->willReturn($product1);
        $availability2->getProduct()->willReturn($product1);
        $availability3->getProduct()->willReturn($product2);
        $availability4->getProduct()->willReturn($product2);

        $availability1->getDate()->willReturn($start);
        $availability2->getDate()->willReturn($end);
        $availability3->getDate()->willReturn($start);
        $availability4->getDate()->willReturn($end);

        $availability1->getStart()->willReturn($start);
        $availability1->getEnd()->willReturn($start);
        $availability2->getStart()->willReturn($end);
        $availability2->getEnd()->willReturn($end);
        $availability3->getStart()->willReturn($start);
        $availability3->getEnd()->willReturn($start);
        $availability4->getStart()->willReturn($end);
        $availability4->getEnd()->willReturn($end);

        $availability1->setProduct($product1)->willReturn($availability1);
        $availability2->setProduct($product1)->willReturn($availability2);
        $availability3->setProduct($product2)->willReturn($availability3);
        $availability4->setProduct($product2)->willReturn($availability4);

        $product1->getIdentifier()->willReturn('product_1');
        $product2->getIdentifier()->willReturn('product_2');

        $collection->addAvailability($availability1)->shouldBeCalled();
        $collection->addAvailability($availability2)->shouldBeCalled();
        $collection->addAvailability($availability3)->shouldBeCalled();
        $collection->addAvailability($availability4)->shouldBeCalled();

        $this->getAvailabilities($partner, $start, $end, [$product1, $product2]);
    }

    function it_get_rates(Partner $partner, Product $product1, Product $product2, EntityManagerInterface $entityManager,
      ProductRateRepository $productRateRepository, RateEntity $rate1, RateEntity $rate2, RateEntity $rate3, RateEntity $rate4,
                          ProductRateCollectionFactory $productRateCollectionFactory, ProductRateCollection $collection)
    {
        $productRateCollectionFactory->create($partner)->willReturn($collection);
        $entityManager->getRepository(RateEntity::class)->willReturn($productRateRepository);
        $start = new \DateTime('2019-01-10');
        $end = new \DateTime('2019-01-11');
        $productRateRepository->findByDateRange($partner, $start, $end, [$product1, $product2])->willReturn([$rate1, $rate2, $rate3, $rate4]);
        $rate1->getProduct()->willReturn($product1);
        $rate2->getProduct()->willReturn($product1);
        $rate3->getProduct()->willReturn($product2);
        $rate4->getProduct()->willReturn($product2);

        $rate1->getDate()->willReturn($start);
        $rate2->getDate()->willReturn($end);
        $rate3->getDate()->willReturn($start);
        $rate4->getDate()->willReturn($end);

        $rate1->getStart()->willReturn($start);
        $rate1->getEnd()->willReturn($start);
        $rate2->getStart()->willReturn($end);
        $rate3->getEnd()->willReturn($end);
        $rate3->getStart()->willReturn($start);
        $rate4->getEnd()->willReturn($start);
        $rate4->getStart()->willReturn($end);
        $rate4->getEnd()->willReturn($end);

        $rate1->setProduct($product1)->willReturn($rate1);
        $rate2->setProduct($product1)->willReturn($rate2);
        $rate3->setProduct($product2)->willReturn($rate3);
        $rate4->setProduct($product2)->willReturn($rate4);

        $product1->getIdentifier()->willReturn('product_1');
        $product2->getIdentifier()->willReturn('product_2');

        $collection->addRate($product1, $rate1)->shouldBeCalled();
        $collection->addRate($product1, $rate2)->shouldBeCalled();
        $collection->addRate($product2, $rate3)->shouldBeCalled();
        $collection->addRate($product2, $rate4)->shouldBeCalled();

        $this->getRates($partner, $start, $end, [$product1, $product2]);
    }

    function it_get_booking(Partner $partner, ChannelManager $channelManager, BookingRepository $bookingRepository, EntityManagerInterface $entityManager,
        Booking $booking1, Booking $booking2, BookingCollectionFactory $bookingCollectionFactory, BookingCollection $collection)
    {
        $bookingCollectionFactory->create()->willReturn($collection);
        $entityManager->getRepository(Booking::class)->willReturn($bookingRepository);
        $start = new \DateTime('2019-01-10');
        $end = new \DateTime('2019-01-11');
        $bookingRepository->findByDateRange($start, $end, [$partner], $channelManager, null)->willReturn([$booking1, $booking2]);
        $collection->setBookings([$booking1, $booking2])->shouldBeCalled()->willReturn($collection);

        $this->getBookings($start, $end, $channelManager, [$partner])->shouldBe($collection);
    }
}
