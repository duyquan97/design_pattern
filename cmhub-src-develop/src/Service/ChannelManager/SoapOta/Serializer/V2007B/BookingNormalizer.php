<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Model\Booking;
use App\Model\BookingInterface;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class BookingNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingNormalizer implements NormalizerInterface
{
    public const DEFAULT_UNIQUE_ID_TYPE = 14;

    /**
     *
     * @var GuestNormalizer
     */
    private $guestNormalizer;

    /**
     *
     * @var BookingProductNormalizer
     */
    private $bookingProductNormalizer;

    /**
     *
     * @param GuestNormalizer          $guestNormalizer          The guest normalizer
     * @param BookingProductNormalizer $bookingProductNormalizer The booking product normalizer
     */
    public function __construct(GuestNormalizer $guestNormalizer, BookingProductNormalizer $bookingProductNormalizer)
    {
        $this->guestNormalizer = $guestNormalizer;
        $this->bookingProductNormalizer = $bookingProductNormalizer;
    }

    /**
     *
     * @param BookingInterface $booking The booking
     * @param array            $context The context
     *
     * @return array|boolean
     */
    public function normalize($booking, array $context = array())
    {
        $products = $guests = [];

        foreach ($booking->getGuests() as $index => $guest) {
            $guests[] = $this->guestNormalizer->normalize($guest, ['index' => ($index + 1)]);
        }

        foreach ($booking->getBookingProducts() as $bookingProduct) {
            $products[] = $this->bookingProductNormalizer->normalize($bookingProduct);
        }

        return [
            'RoomStayReservation' => true,
            'ResStatus'           => $booking->getStatus(),
            'CreateDateTime'      => $booking->getCreatedAt()->format('c'),
            'LastModifyDateTime'  => $booking->getUpdatedAt()->format('c'),
            'UniqueID'            => [
                'Type' => self::DEFAULT_UNIQUE_ID_TYPE,
                'ID'   => $booking->getReservationId(),
            ],
            'ResGlobalInfo'       => [
                'HotelReservationIDs' => [
                    'HotelReservationID' => [
                        [
                            'ResID_Value' => $booking->getReservationId(),
                        ],
                    ],
                ],
                'TimeSpan'            => [
                    'Start'    => $booking->getStartDate()->format('Y-m-d'),
                    'End'      => $booking->getEndDate()->format('Y-m-d'),
                    'Duration' => $booking->getStartDate()->diff($booking->getEndDate())->format('%a'),
                ],
                'Total'               => [
                    'AmountAfterTax' => $booking->getTotalAmount(),
                    'CurrencyCode'   => $booking->getCurrency(),
                ],
                'SpecialRequests'     => [
                    [
                        'Text' => $booking->getRequests(),
                    ],
                ],
                'Comments'            => [
                    [
                        'Text' => $booking->getComments(),
                    ],
                ],
            ],
            'RoomStays'           => [
                'RoomStay' => $products,
            ],
            'ResGuests'           => [
                'ResGuest' => $guests,
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
