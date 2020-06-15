<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Model\BookingCollection;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class BookingCollectionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingCollectionNormalizer implements NormalizerInterface
{
    /**
     *
     * @var BookingNormalizer
     */
    private $bookingNormalizer;

    /**
     *
     * @param BookingNormalizer $bookingNormalizer The booking normalizer
     */
    public function __construct(BookingNormalizer $bookingNormalizer)
    {
        $this->bookingNormalizer = $bookingNormalizer;
    }

    /**
     *
     * @param BookingCollection $bookings
     * @param array             $context
     *
     * @return array
     */
    public function normalize($bookings, array $context = array())
    {
        $reservations = [];
        foreach ($bookings->getBookings() as $booking) {
            $reservations[] = $this->bookingNormalizer->normalize($booking);
        }

        return [
            'ReservationsList' => [
                'HotelReservation' => $reservations,
            ],
        ];
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return void
     */
    public function denormalize($data, array $context = array())
    {
        // TODO: Implement denormalize() method.
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return BookingCollection::class === $class;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return false;
    }
}
