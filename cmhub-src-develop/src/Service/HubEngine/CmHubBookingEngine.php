<?php

namespace App\Service\HubEngine;

use App\Entity\Availability;
use App\Entity\Booking;
use App\Entity\ChannelManager;
use App\Entity\ProductRate;
use App\Entity\TransactionChannel;
use App\Message\Factory\AvailabilityUpdatedFactory as AvailabilityUpdateFactory;
use App\Message\Factory\RateUpdatedFactory as RateUpdateFactory;
use App\Model\AvailabilitySource;
use App\Model\BookingCollection;
use App\Model\Factory\BookingCollectionFactory;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductAvailabilityInterface;
use App\Model\ProductInterface;
use App\Model\ProductRateCollection;
use App\Model\ProductRateCollectionInterface;
use App\Model\ProductRateInterface;
use App\Model\Rate;
use App\Model\RateInterface;
use App\Service\BookingEngineInterface;
use App\Service\Loader\ProductLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class CmHubBookingEngine
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class CmHubBookingEngine implements BookingEngineInterface
{
    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var AvailabilityUpdateFactory
     */
    private $availabilityMessageFactory;

    /**
     * @var RateUpdateFactory
     */
    private $priceMessageFactory;

    /**
     * @var ProductAvailabilityCollectionFactory
     */
    private $productAvailabilityCollectionFactory;

    /**
     * @var ProductRateCollectionFactory
     */
    private $productRateCollectionFactory;

    /**
     * @var BookingCollectionFactory
     */
    private $bookingCollectionFactory;

    /**
     * CmHubBookingEngine constructor.
     *
     * @param EntityManagerInterface               $entityManager
     * @param ProductLoader                        $productLoader
     * @param MessageBusInterface                  $messageBus
     * @param AvailabilityUpdateFactory            $availabilityMessageFactory
     * @param RateUpdateFactory                    $priceMessageFactory
     * @param ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory
     * @param ProductRateCollectionFactory         $productRateCollectionFactory
     * @param BookingCollectionFactory             $bookingCollectionFactory
     */
    public function __construct(EntityManagerInterface $entityManager, ProductLoader $productLoader, MessageBusInterface $messageBus, AvailabilityUpdateFactory $availabilityMessageFactory, RateUpdateFactory $priceMessageFactory, ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory, ProductRateCollectionFactory $productRateCollectionFactory, BookingCollectionFactory $bookingCollectionFactory)
    {
        $this->entityManager = $entityManager;
        $this->productLoader = $productLoader;
        $this->messageBus = $messageBus;
        $this->availabilityMessageFactory = $availabilityMessageFactory;
        $this->priceMessageFactory = $priceMessageFactory;
        $this->productAvailabilityCollectionFactory = $productAvailabilityCollectionFactory;
        $this->productRateCollectionFactory = $productRateCollectionFactory;
        $this->bookingCollectionFactory = $bookingCollectionFactory;
    }

    /**
     *
     * @param ProductAvailabilityCollectionInterface $productAvailabilities
     *
     * @return ProductAvailabilityCollectionInterface
     */
    public function updateAvailability(ProductAvailabilityCollectionInterface $productAvailabilities): ProductAvailabilityCollectionInterface
    {
        $collection = $this->productAvailabilityCollectionFactory->create($productAvailabilities->getPartner());
        $collection->setSource($productAvailabilities->getSource());

        /* @var ProductAvailabilityInterface $availability */
        foreach ($productAvailabilities->getProductAvailabilities() as $availability) {
            $availabilities = $availability->getAvailabilities();
            $updatedEntities = [];
            foreach ($availabilities as $data) {
                $start = clone $data->getStart();
                while ($start <= $data->getEnd()) {
                    /* @var Availability $entity */
                    $entity = $this
                        ->entityManager
                        ->getRepository(Availability::class)
                        ->findOneBy(
                            [
                                'date'    => $start,
                                'product' => $availability->getProduct(),
                            ]
                        );

                    if (!$entity) {
                        $entity = new Availability();
                    }

                    $entity
                        ->setProduct($data->getProduct())
                        ->setPartner($productAvailabilities->getPartner())
                        ->setDate(clone $start)
                        ->setTransaction($productAvailabilities->getTransaction());

                    if (null !== $data->getStock()) {
                        $entity->setStock($data->getStock());
                    }

                    if (null !== $data->isStopSale()) {
                        $entity->setStopSale($data->isStopSale());
                    }

                    $collection->addAvailability($entity);

                    $this->entityManager->persist($entity);
                    $updatedEntities[] = $entity;

                    $start->modify('+1 day');
                }
            }

            $this->entityManager->flush();

            // Send to the queue
            $this
                ->messageBus
                ->dispatch(
                    $this->availabilityMessageFactory->create(
                        array_map(
                            function (Availability $entity) {
                                return $entity->getId();
                            },
                            $updatedEntities
                        ),
                        $productAvailabilities->getChannel()
                    )
                );
        }

        if (null === $collection->getPartner()->getConnectedAt() && AvailabilitySource::CM === $productAvailabilities->getSource()) {
            $collection->getPartner()->setConnectedAt(date_create());
            $this->entityManager->persist($collection->getPartner());
            $this->entityManager->flush();
        }

        return $collection;
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param \DateTime        $start
     * @param \DateTime        $end
     * @param array            $products
     *
     * @return ProductAvailabilityCollectionInterface
     */
    public function getAvailabilities(PartnerInterface $partner, \DateTime $start, \DateTime $end, array $products = array()): ProductAvailabilityCollectionInterface
    {
        if (!$products) {
            $products = $this->productLoader->getByPartner($partner)->toArray();
        }

        $availabilities = $this->entityManager->getRepository(Availability::class)->findByDateRange($partner, $start, $end, $products);
        $collection = $this->productAvailabilityCollectionFactory->create($partner);

        foreach ($products as $product) {
            $dateStart = clone $start;
            while ($dateStart <= $end) {
                // TODO: Provisional workaround. Calls to db must not be inside an array.
                $availability = $this->exists($availabilities, $dateStart, $product);
                if (!$availability) {
                    $availability = (new Availability())
                        ->setStock(0)
                        ->setProduct($product)
                        ->setDate(clone $dateStart)
                        ->setPartner($partner)
                        ->setCreatedAt(date_create())
                        ->setUpdatedAt(date_create());
                }

                $collection->addAvailability($availability);

                $dateStart->modify('+1 day');
            }
        }

        return $collection;
    }

    /**
     *
     * @param \DateTime           $start
     * @param \DateTime           $end
     * @param ChannelManager|null $channelManager
     * @param array               $partners
     * @param string              $dateType
     *
     * @return BookingCollection
     */
    public function getBookings(\DateTime $start, \DateTime $end, ChannelManager $channelManager = null, $partners = array(), string $dateType = null): BookingCollection
    {
        $bookings = $this->entityManager->getRepository(Booking::class)->findByDateRange($start, $end, $partners, $channelManager, $dateType);

        return $this->bookingCollectionFactory->create()->setBookings($bookings);
    }

    /**
     *
     * @param ProductRateCollectionInterface $productRateCollection
     *
     * @return ProductRateCollectionInterface
     */
    public function updateRates(ProductRateCollectionInterface $productRateCollection): ProductRateCollectionInterface
    {
        /* @var ProductRateInterface $productRate */
        foreach ($productRateCollection->getProductRates() as $productRate) {
            $partner = $productRateCollection->getPartner();
            $updatedEntities = [];
            foreach ($productRate->getRates() as $rate) {
                $start = clone $rate->getStart();

                while ($start <= $rate->getEnd()) {
                    $entity = new ProductRate();
                    $exists = $this
                        ->entityManager
                        ->getRepository(ProductRate::class)
                        ->findOneBy(
                            [
                                'partner' => $partner,
                                'date'    => $start->setTime(0, 0, 0),
                                'product' => $productRate->getProduct(),
                            ]
                        );
                    if ($exists) {
                        $entity = $exists;
                    }

                    $entity
                        ->setPartner($partner)
                        ->setAmount($rate->getAmount())
                        ->setDate(clone $start)
                        ->setProduct($productRate->getProduct())
                        ->setTransaction($productRateCollection->getTransaction());

                    $this->entityManager->persist($entity);
                    $updatedEntities[] = $entity;

                    $start->modify('+1 day');
                }
            }

            $this->entityManager->flush();

            // Send to the queue
            $this
                ->messageBus
                ->dispatch(
                    $this->priceMessageFactory->create(
                        array_map(function (ProductRate $entity) {
                            return $entity->getId();
                        }, $updatedEntities),
                        $productRateCollection->getChannel()
                    )
                );
        }

        return $productRateCollection;
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param \DateTime        $start
     * @param \DateTime        $end
     * @param array            $products
     *
     * @return ProductRateCollection
     */
    public function getRates(PartnerInterface $partner, \DateTime $start, \DateTime $end, array $products = array()): ProductRateCollection
    {
        $rates = $this->entityManager->getRepository(ProductRate::class)->findByDateRange($partner, $start, $end, $products);

        $productsDb = (sizeof($products) > 0) ? $products : $this->productLoader->getByPartner($partner);

        $collection = $this->productRateCollectionFactory->create($partner);

        foreach ($productsDb as $product) {
            $dateStart = clone $start;
            while ($dateStart <= $end) {
                /* @var RateInterface $rate */
                $rate = $this->exists($rates, $dateStart, $product);
                if (!$rate) {
                    $rate = (new Rate())
                        ->setAmount(0)
                        ->setProduct($product)
                        ->setStart(clone $dateStart)
                        ->setEnd(clone $dateStart);
                }

                $collection->addRate($product, $rate);

                $dateStart->modify('+1 day');
            }
        }

        return $collection;
    }

    /**
     *
     * @param array            $availabilities
     * @param \DateTime        $date
     * @param ProductInterface $product
     *
     * @return bool|mixed
     */
    private function exists(array $availabilities, \DateTime $date, ProductInterface $product)
    {
        foreach ($availabilities as $availability) {
            if ($availability->getDate()->format('Y-m-d') === $date->format('Y-m-d') && $availability->getProduct()->getIdentifier() === $product->getIdentifier()) {
                return $availability;
            }
        }

        return false;
    }
}
