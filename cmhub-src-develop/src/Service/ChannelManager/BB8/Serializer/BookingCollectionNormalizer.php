<?php

namespace App\Service\ChannelManager\BB8\Serializer;

use App\Model\BookingCollection;
use App\Model\RatePlanCode;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class BookingCollectionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingCollectionNormalizer implements NormalizerInterface
{
    /**
     *
     * @param BookingCollection $bookingCollection
     * @param array             $context
     *
     * @return array
     */
    public function normalize($bookingCollection, array $context = array())
    {
        $bookings = [];

        foreach ($bookingCollection->getBookings() as $booking) {
            $bookingsProducts = [];
            foreach ($booking->getBookingProducts() as $bookingProduct) {
                foreach ($bookingProduct->getRates() as $rate) {
                    $bookingsProducts[] = [
                        'date'               => $rate->getStart()->format('Y-m-d'),
                        'externalRateBandId' => RatePlanCode::SBX,
                        'externalRoomId'     => $rate->getProduct()->getIdentifier(),
                    ];
                }
            }

            $bookings[] = [
                'externalId'          => $booking->getReservationId(),
                'dates'               => $bookingsProducts,
                'bookingStatus'       => strtolower($booking->getStatus()),
                'bookingType'         => 'instant',
                'externalPartnerId'   => $booking->getPartner()->getIdentifier(),
                'bookingStart'        => $booking->getStartDate()->format('Y-m-d'),
                'bookingEnd'          => $booking->getEndDate()->format('Y-m-d'),
                'externalUpdatedFrom' => $booking->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            ];
        }

        return $bookings;
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
        throw new MethodNotImplementedException('Method BookingCollection::denormalize is not implemented');
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        if (BookingCollection::class === $class) {
            return true;
        }

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
        return false;
    }
}
