<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\ChannelManager;
use App\Entity\Factory\TransactionFactory;
use App\Entity\TransactionChannel;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Message\Factory\TransactionScheduledFactory;
use App\Model\BookingCollection;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductRateCollection;
use App\Model\ProductRateCollectionInterface;
use App\Service\Chaining\ChainingHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * Class BookingEngineManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingEngineManager implements BookingEngineInterface
{
    /**
     * @var BookingEngineInterface[]
     */
    private $bookingEngines;

    /**
     *
     * @var ChainingHelper
     */
    private $chainingHelper;

    /**
     *
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * BookingEngineManager constructor.
     *
     * @param ChainingHelper         $chainingHelper
     * @param TransactionFactory     $transactionFactory
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ChainingHelper $chainingHelper, TransactionFactory $transactionFactory, EntityManagerInterface $entityManager)
    {
        $this->chainingHelper = $chainingHelper;
        $this->transactionFactory = $transactionFactory;
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @param ProductAvailabilityCollectionInterface $productAvailabilities
     *
     * @return ProductAvailabilityCollectionInterface
     */
    public function updateAvailability(ProductAvailabilityCollectionInterface $productAvailabilities): ProductAvailabilityCollectionInterface
    {
        if ($productAvailabilities->isEmpty()) {
            return $productAvailabilities;
        }

        $productAvailabilities = $this->chainingHelper->chainAvailabilities($productAvailabilities);
        foreach ($this->bookingEngines as $engine) {
            $engine->updateAvailability($productAvailabilities);
        }

        return $productAvailabilities;
    }

    /**
     *
     * @param ProductRateCollectionInterface $productRateCollection
     *
     * @return ProductRateCollectionInterface
     */
    public function updateRates(ProductRateCollectionInterface $productRateCollection): ProductRateCollectionInterface
    {
        if ($productRateCollection->isEmpty()) {
            return $productRateCollection;
        }

        $productRateCollection = $this->chainingHelper->chainRates($productRateCollection);
        foreach ($this->bookingEngines as $engine) {
            $engine->updateRates($productRateCollection);
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
     * @return ProductAvailabilityCollectionInterface
     */
    public function getAvailabilities(PartnerInterface $partner, \DateTime $start, \DateTime $end, array $products = array()): ProductAvailabilityCollectionInterface
    {
        return current($this->bookingEngines)->getAvailabilities($partner, $start, $end, $products);
    }

    /**
     *
     * @param \DateTime                 $start
     * @param \DateTime                 $end
     * @param ChannelManager|null       $channelManager
     * @param array                     $partners
     * @param string                    $dateType
     *
     * @return BookingCollection
     */
    public function getBookings(\DateTime $start, \DateTime $end, ChannelManager $channelManager = null, $partners = array(), string $dateType = null): BookingCollection
    {
        $bookingCollections = current($this->bookingEngines)->getBookings($start, $end, $channelManager, $partners, $dateType);

        /** @var Booking $booking */
        foreach ($bookingCollections->getBookings() as $booking) {
            if (!$booking->getTransaction()) {
                $transaction = $this
                    ->transactionFactory
                    ->create(
                        TransactionType::BOOKING,
                        $booking->getPartner()->getChannelManager()->getIdentifier(),
                        TransactionStatus::SUCCESS,
                        $booking->getPartner()
                    )
                    ->setStatusCode(200)
                    ->setSentAt(date_create());

                $booking->setTransaction($transaction);
                $this->entityManager->persist($booking);
            }
        }
        $this->entityManager->flush();

        return $bookingCollections;
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
        return current($this->bookingEngines)->getRates($partner, $start, $end, $products);
    }

    /**
     *
     * @param BookingEngineInterface $bookingEngine
     *
     * @return void
     */
    public function addBookingEngine(BookingEngineInterface $bookingEngine)
    {
        $this->bookingEngines[] = $bookingEngine;
    }
}
