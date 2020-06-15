<?php

namespace spec\App\Service\Iresa;

use App\Entity\Partner;
use App\Entity\Product;
use App\Exception\IresaClientException;
use App\Exception\NormalizerNotFoundException;
use App\Model\BookingCollection;
use App\Model\BookingInterface;
use App\Model\BookingProductInterface;
use App\Model\Factory\BookingCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductCollection;
use App\Model\ProductInterface;
use App\Model\ProductRateCollection;
use App\Model\ProductRateCollectionInterface;
use App\Repository\ProductRepository;
use App\Service\Iresa\IresaApi;
use App\Service\Iresa\IresaBookingEngine;
use App\Service\Iresa\Serializer\IresaSerializer;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IresaBookingEngineSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IresaBookingEngine::class);
    }

    function let(IresaApi $iresaApi, IresaSerializer $iresaSerializer, EntityManagerInterface $entityManager, CmhubLogger $logger, BookingCollectionFactory $bookingCollectionFactory)
    {
        $this->beConstructedWith($iresaApi, $iresaSerializer, $entityManager, $logger, $bookingCollectionFactory);
    }

    function it_updates_availability(
        ProductAvailabilityCollectionInterface $availabilityCollection,
        IresaApi $iresaApi
    )
    {
        $iresaApi->updateAvailabilities($availabilityCollection)->shouldBeCalled();
        $this->updateAvailability($availabilityCollection);
    }


    function it_gets_availability_from_iresa_and_denormalizes_response(ProductAvailabilityCollectionInterface $availabilityCollection, \stdClass $availabilities, IresaApi $iresaApi, IresaSerializer $iresaSerializer, PartnerInterface $partner, ProductInterface $product, ProductInterface $product1)
    {
        $iresaApi->getAvailabilities($partner, $start = new \DateTime(), $end = new \DateTime('+1 day'), $products = [
            $product,
            $product1
        ])->shouldBeCalled()->willReturn($availabilities);
        $iresaSerializer->denormalize($availabilities, ProductAvailabilityCollection::class, ['partner' => $partner])->willReturn($availabilityCollection);
        $this->getAvailabilities($partner, $start, $end, $products)->shouldBe($availabilityCollection);
    }

    function it_gets_bookings_from_iresa_sets_master_room_and_denormalizes_response(
        PartnerInterface $partner,
        IresaApi $iresaApi,
        \stdClass $bookings,
        IresaSerializer $iresaSerializer,
        BookingCollection $bookingCollection,
        BookingInterface $booking,
        BookingInterface $booking1,
        BookingProductInterface $bookingProduct,
        BookingProductInterface $bookingProduct1,
        ProductInterface $product,
        ProductInterface $product1,
        ProductInterface $masterProduct,
        BookingCollectionFactory $bookingCollectionFactory,
        BookingCollection $bookingCollectionFinal
    )
    {
        $bookingCollectionFactory->create()->shouldBeCalled()->willReturn($bookingCollectionFinal);
        $iresaApi->getBookings($partner, $start = new \DateTime(), $end = new \DateTime('+10 day'))->shouldBeCalled()->willReturn($bookings);
        $bookingCollection->getBookings()->willReturn([
            $booking,
            $booking1
        ]);
        $booking->getBookingProducts()->willReturn([$bookingProduct]);
        $booking1->getBookingProducts()->willReturn([$bookingProduct1]);
        $bookingProduct->getProduct()->willReturn($product);
        $bookingProduct1->getProduct()->willReturn($product1);
        $product->isMaster()->willReturn(false);
        $product1->isMaster()->willReturn(true);
        $product->getMasterProduct()->willReturn($masterProduct);
        $bookingProduct->setProduct($masterProduct)->shouldBeCalled();
        $bookingProduct1->setProduct(Argument::any())->shouldNotBeCalled();
        $bookingCollectionFinal->addBooking($booking)->shouldBeCalled();
        $bookingCollectionFinal->addBooking($booking1)->shouldBeCalled();

        $iresaSerializer->denormalize($bookings, BookingCollection::class, ['partner' => $partner])->willReturn($bookingCollection);
        $this->getBookings($start, $end, null, [$partner])->shouldBe($bookingCollectionFinal);
    }

    function it_updates_rates_on_iresa_calling_iresa_api(
        ProductRateCollectionInterface $productRateCollection,
        IresaApi $iresaApi
    )
    {
        $iresaApi->updateRates($productRateCollection)->shouldBeCalled()->willReturn($productRateCollection);
        $this->updateRates($productRateCollection)->shouldBe($productRateCollection);
    }

    function it_gets_rates_and_denormalizes_response(IresaApi $iresaApi, PartnerInterface $partner, ProductInterface $product, ProductInterface $product1, IresaSerializer $iresaSerializer, ProductRateCollection $productRateCollection)
    {
        $iresaApi
            ->getRates(
                $partner,
                $start = new \DateTime(),
                $end = new \DateTime('+2 day'),
                $products = [
                    $product,
                    $product1
                ]
            )->shouldBeCalled()
            ->willReturn($rates = ['array' => 'rates']);
        $iresaSerializer->denormalize($rates, ProductRateCollection::class, ['partner' => $partner])->willReturn($productRateCollection);
        $this->getRates($partner, $start, $end, $products)->shouldBe($productRateCollection);
    }

    function it_pulls_products(EntityManagerInterface $entityManager, ProductRepository $productRepository, Partner $partner,
        Product $product1, Product $product2, Product $product3, IresaSerializer $iresaSerializer, ProductCollection $collection, IresaApi $iresaApi)
    {
        $entityManager->getRepository(Product::class)->willReturn($productRepository);
        $productRepository->findBy(['partner' => $partner])->willReturn([$product1, $product2]);
        $iresaApi
            ->getProducts(
                $partner
            )->shouldBeCalled()
            ->willReturn($products = ['array' => 'products']);
        $iresaSerializer->denormalize(
            $products,
            ProductCollection::class,
            ['partner' => $partner, ]
        )->willReturn($collection);


        $collection->getProducts()->willReturn([$product1, $product2]);
        $collection->isEmpty()->willReturn(false);
        $collection->contains($product1)->willReturn(false);
        $collection->contains($product2)->willReturn(true);
        $product1->hasLinkedProducts()->willReturn(false);
        $product1->isMaster()->willReturn(true);

        $product1->setSellable(false)->shouldBeCalled()->willReturn($product1);
        $product1->setReservable(false)->shouldBeCalled()->willReturn($product1);

        $product2->setSellable(false)->shouldNotBeCalled();
        $product2->setReservable(false)->shouldNotBeCalled();

        $entityManager->persist($product1)->shouldBeCalled();
        $entityManager->persist($product2)->shouldBeCalled();

        $entityManager->flush()->shouldBeCalled();

        $this->pullProducts($partner)->shouldBe($collection);
    }

    function it_throws_guzzle_exception_when_pull_products(EntityManagerInterface $entityManager, ProductRepository $productRepository, Partner $partner,
        Product $product1, Product $product2, IresaApi $iresaApi, CmhubLogger $logger)
    {
        $entityManager->getRepository(Product::class)->willReturn($productRepository);
        $productRepository->findBy(['partner' => $partner])->willReturn([$product1, $product2]);
        $iresaApi
            ->getProducts(
                $partner
            )->willThrow(new IresaClientException('exception', 500, 'response'));

        $logger->addRecord(
            \Monolog\Logger::INFO,
            'exception',
            [
                LogKey::TYPE_KEY    => LogType::EXCEPTION_TYPE,
                LogKey::EX_TYPE_KEY => 'unknown',
                LogKey::MESSAGE_KEY => 'exception',
            ],
            $this
        )->shouldBeCalled();

        $this->shouldThrow(IresaClientException::class)->during('pullProducts', [$partner]);

    }

    function it_throws_exception_when_pull_products(EntityManagerInterface $entityManager, ProductRepository $productRepository, Partner $partner,
       Product $product1, Product $product2, IresaApi $iresaApi, CmhubLogger $logger, IresaSerializer $iresaSerializer)
    {
        $entityManager->getRepository(Product::class)->willReturn($productRepository);
        $productRepository->findBy(['partner' => $partner])->willReturn([$product1, $product2]);
        $iresaApi
            ->getProducts(
                $partner
            )->shouldBeCalled()
            ->willReturn($products = ['array' => 'products']);
        $iresaSerializer->denormalize(
            $products,
            ProductCollection::class,
            ['partner' => $partner, ]
        )->willThrow(new NormalizerNotFoundException());

        $logger->addRecord(
            \Monolog\Logger::INFO,
            'The serializer requested has not been found',
            [
                LogKey::TYPE_KEY    => LogType::EXCEPTION_TYPE,
                LogKey::EX_TYPE_KEY => 'unknown',
                LogKey::MESSAGE_KEY => 'The serializer requested has not been found',
            ],
            $this
        )->shouldBeCalled();

        $this->shouldThrow(NormalizerNotFoundException::class)->during('pullProducts', [$partner]);
    }
}
