<?php

namespace App\Service\Iresa;

use App\Entity\ChannelManager;
use App\Entity\Product;
use App\Exception\IresaClientException;
use App\Exception\NormalizerNotFoundException;
use App\Model\BookingCollection;
use App\Model\Factory\BookingCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductCollection;
use App\Model\ProductInterface;
use App\Model\ProductRateCollection;
use App\Model\ProductRateCollectionInterface;
use App\Service\BookingEngineInterface;
use App\Service\Iresa\Serializer\IresaSerializer;
use App\Utils\Monolog;
use App\Utils\Monolog\CmhubLogger;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class IresaBookingEngine
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaBookingEngine implements BookingEngineInterface
{
    /**
     *
     * @var IresaApi
     */
    private $iresaApi;

    /**
     *
     * @var IresaSerializer
     */
    private $iresaSerializer;

    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     *
     * @var BookingCollectionFactory
     */
    private $bookingCollectionFactory;

    /**
     * IresaBookingEngine constructor.
     *
     * @param IresaApi                  $iresaApi
     * @param IresaSerializer           $iresaSerializer
     * @param EntityManagerInterface    $entityManager
     * @param CmhubLogger               $logger
     * @param BookingCollectionFactory  $bookingCollectionFactory
     */
    public function __construct(IresaApi $iresaApi, IresaSerializer $iresaSerializer, EntityManagerInterface $entityManager, CmhubLogger $logger, BookingCollectionFactory $bookingCollectionFactory)
    {
        $this->iresaApi = $iresaApi;
        $this->iresaSerializer = $iresaSerializer;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->bookingCollectionFactory = $bookingCollectionFactory;
    }

    /**
     *
     * @param ProductAvailabilityCollectionInterface $collection
     *
     * @return ProductAvailabilityCollectionInterface
     *
     * @throws IresaClientException
     * @throws NormalizerNotFoundException
     */
    public function updateAvailability(ProductAvailabilityCollectionInterface $collection): ProductAvailabilityCollectionInterface
    {
        return $this->iresaApi->updateAvailabilities($collection);
    }

    /**
     *
     * @param PartnerInterface   $partner
     * @param \DateTime          $start
     * @param \DateTime          $end
     * @param ProductInterface[] $products
     *
     * @return ProductAvailabilityCollectionInterface
     *
     * @throws IresaClientException
     * @throws NormalizerNotFoundException
     */
    public function getAvailabilities(PartnerInterface $partner, \DateTime $start, \DateTime $end, array $products = []): ProductAvailabilityCollectionInterface
    {
        $availabilities = $this->iresaApi->getAvailabilities($partner, $start, $end, $products);

        /* @var ProductAvailabilityCollection $collection */
        $collection = $this->iresaSerializer->denormalize(
            $availabilities,
            ProductAvailabilityCollection::class,
            [
                'partner' => $partner,
            ]
        );

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
     *
     * @throws IresaClientException
     * @throws NormalizerNotFoundException
     */
    public function getBookings(\DateTime $start, \DateTime $end, ChannelManager $channelManager = null, $partners = array(), string $dateType = null): BookingCollection
    {
        $bookingCollection = $this->bookingCollectionFactory->create();
        foreach ($partners as $partner) {
            /** @var BookingCollection $collection */
            $collection = $this->iresaSerializer->denormalize(
                $this->iresaApi->getBookings($partner, $start, $end),
                BookingCollection::class,
                [
                    'partner' => $partner,
                ]
            );
            foreach ($collection->getBookings() as $booking) {
                foreach ($booking->getBookingProducts() as $bookingProduct) {
                    if (!$bookingProduct->getProduct()->isMaster()) {
                        $bookingProduct->setProduct($bookingProduct->getProduct()->getMasterProduct());
                    }
                }
                $bookingCollection->addBooking($booking);
            }
        }

        return $bookingCollection;
    }

    /**
     *
     * @param ProductRateCollectionInterface $productRateCollection
     *
     * @return ProductRateCollectionInterface
     *
     * @throws IresaClientException
     */
    public function updateRates(ProductRateCollectionInterface $productRateCollection): ProductRateCollectionInterface
    {
        return $this->iresaApi->updateRates($productRateCollection);
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param \DateTime        $start
     * @param \DateTime        $end
     * @param array            $products
     *
     * @return ProductRateCollection
     *
     * @throws IresaClientException
     * @throws NormalizerNotFoundException
     */
    public function getRates(PartnerInterface $partner, \DateTime $start, \DateTime $end, array $products = array()): ProductRateCollection
    {
        $rates = $this->iresaApi->getRates($partner, $start, $end, $products);

        return $this->iresaSerializer->denormalize($rates, ProductRateCollection::class, ['partner' => $partner]);
    }

    /**
     *
     * @param PartnerInterface $partner
     *
     * @return ProductCollection
     *
     * @throws GuzzleException
     */
    public function pullProducts(PartnerInterface $partner): ProductCollection
    {
        $partnerProducts = $this->entityManager->getRepository(Product::class)->findBy(['partner' => $partner]);

        try {
            /* @var ProductCollection $collection */
            $collection = $this->iresaSerializer->denormalize(
                $this->iresaApi->getProducts($partner),
                ProductCollection::class,
                [
                    'partner' => $partner,
                ]
            );

            if (!$collection->isEmpty()) {
                /** @var Product $partnerProduct */
                foreach ($partnerProducts as $partnerProduct) {
                    if (!$collection->contains($partnerProduct) && !$partnerProduct->hasLinkedProducts() && $partnerProduct->isMaster()) {
                        $partnerProduct->setSellable(false)->setReservable(false);
                        $this->entityManager->persist($partnerProduct);
                    }
                }

                foreach ($collection->getProducts() as $product) {
                    $this->entityManager->persist($product);
                }

                $this->entityManager->flush();
            }
        } catch (GuzzleException | \Exception $exception) {
            $this->logger->addRecord(
                \Monolog\Logger::INFO,
                $exception->getMessage(),
                [
                    Monolog\LogKey::TYPE_KEY    => Monolog\LogType::EXCEPTION_TYPE,
                    Monolog\LogKey::EX_TYPE_KEY => 'unknown',
                    Monolog\LogKey::MESSAGE_KEY => $exception->getMessage(),
                ],
                $this
            );

            throw $exception;
        }

        return $collection;
    }
}
