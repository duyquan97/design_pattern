<?php

namespace App\Service\Iresa\Serializer;

use App\Entity\Partner;
use App\Exception\ProductNotFoundException;
use App\Model\Booking;
use App\Model\Factory\BookingFactory;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class BookingNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingNormalizer implements NormalizerInterface
{
    /**
     *
     * @var BookingProductNormalizer
     */
    private $bookingProductNormalizer;

    /**
     *
     * @var BookingFactory
     */
    private $bookingFactory;

    /**
     * BookingNormalizer constructor.
     *
     * @param BookingProductNormalizer $bookingProductNormalizer
     * @param BookingFactory           $bookingFactory
     */
    public function __construct(BookingProductNormalizer $bookingProductNormalizer, BookingFactory $bookingFactory)
    {
        $this->bookingProductNormalizer = $bookingProductNormalizer;
        $this->bookingFactory = $bookingFactory;
    }

    /**
     *
     * @param mixed $object
     * @param array $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        // TODO: Implement normalize() method.
    }

    /**
     *
     * @param \stdClass $booking
     * @param array     $context
     *
     * @return Booking
     *
     * @throws ProductNotFoundException
     */
    public function denormalize($booking, array $context = array())
    {
        /* @var Partner $partner */
        $partner = $context['partner'];

        $bookingModel = $this
            ->bookingFactory
            ->create()
            ->setStatus($booking->status)
            ->setStartDate(new \DateTime($booking->dateStart))
            ->setEndDate(new \DateTime($booking->dateEnd))
            ->setCreatedAt(new \DateTime($booking->dateCreation))
            ->setUpdatedAt(new \DateTime($booking->dateLastModified))
            ->setTotalAmount($booking->totalAmount)
            ->setCurrency($partner->getCurrency())
            ->setReservationId($booking->reservationId);

        foreach ($booking->roomTypes as $roomType) {
            $bookingProduct = $this->bookingProductNormalizer->denormalize($roomType, $context);
            if (!$bookingProduct) {
                continue;
            }

            $bookingProduct->setBooking($bookingModel);

            $bookingModel->addBookingProduct($bookingProduct);
        }

        return $bookingModel;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return false;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return $class === Booking::class;
    }
}
