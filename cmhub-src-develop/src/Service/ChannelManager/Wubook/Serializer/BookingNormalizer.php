<?php

namespace App\Service\ChannelManager\Wubook\Serializer;

use App\Entity\Booking;
use App\Model\BookingInterface;
use App\Booking\Model\BookingStatus;
use App\Model\WubookBookingStatus;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class BookingNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingNormalizer implements NormalizerInterface
{
    /**
     * @var BookingProductNormalizer
     */
    private $bookingProductNormalizer;

    /**
     * @var GuestNormalizer
     */
    private $guestNormalizer;

    /**
     * BookingNormalizer constructor.
     *
     * @param BookingProductNormalizer $bookingProductNormalizer
     * @param GuestNormalizer          $guestNormalizer
     */
    public function __construct(BookingProductNormalizer $bookingProductNormalizer, GuestNormalizer $guestNormalizer)
    {
        $this->bookingProductNormalizer = $bookingProductNormalizer;
        $this->guestNormalizer = $guestNormalizer;
    }

    /**
     *
     * @param BookingInterface $booking
     * @param array            $context
     *
     * @return array
     */
    public function normalize($booking, array $context = array())
    {
        $startDate = $booking->getStartDate();
        $endDate = $booking->getEndDate();

        $bookingData = [
            'booking_id'     => $booking->getReservationId(),
            'status'         => $booking->isConfirmed() ? WubookBookingStatus::NEW : WubookBookingStatus::CANCEL,
            'created'        => $booking->getCreatedAt()->format('Y-m-d h:i:s'),
            'modified'       => $booking->getUpdatedAt()->format('Y-m-d h:i:s'),
            'utc_offset'     => '+0000',
            'hotel_id'       => $booking->getPartner()->getIdentifier(),
            'currency'       => $booking->getCurrency(),
            'arrival_date'   => $startDate->format('Y-m-d'),
            'departure_date' => $endDate->format('Y-m-d'),
            'arrival_hour'   => $startDate->format('h:i'),
            'departure_hour' => $endDate->format('h:i'),
            'rooms'          => [],
            'customer'       => [],
            'notes'          => $booking->getComments(),
            'total_price'    => $booking->getTotalAmount(),
        ];

        foreach ($booking->getGuests() as $guest) {
            if ($guest->isMain()) {
                $bookingData['customer'] = $this->guestNormalizer->normalize($guest);
            }
        }

        foreach ($booking->getBookingProducts() as $bookingProduct) {
            $bookingData['rooms'][] = $this->bookingProductNormalizer->normalize($bookingProduct);
        }

        return $bookingData;
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return mixed|void
     */
    public function denormalize($data, array $context = array())
    {
        throw new MethodNotImplementedException('Method Booking::denormalize is not implemented');
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return Booking::class === $class;
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
