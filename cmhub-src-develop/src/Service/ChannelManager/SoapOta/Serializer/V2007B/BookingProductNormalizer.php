<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Model\BookingProduct;
use App\Model\RatePlanCode;
use App\Service\Serializer\NormalizerInterface;
use App\Utils\OTAAgeFormatter;

/**
 * Class BookingProductNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProductNormalizer implements NormalizerInterface
{
    /**
     *
     * @var RateNormalizer
     */
    private $rateNormalizer;

    /**
     *
     * @param RateNormalizer $rateNormalizer The rate normalizer
     */
    public function __construct(RateNormalizer $rateNormalizer)
    {
        $this->rateNormalizer = $rateNormalizer;
    }

    /**
     *
     * @param BookingProduct $bookingProduct
     * @param array          $context
     *
     * @return array
     */
    public function normalize($bookingProduct, array $context = array())
    {
        $rates = [];
        foreach ($bookingProduct->getRates() as $rate) {
            $rates = array_merge($rates, $this->rateNormalizer->normalize($rate));
        }

        $resGuestRPHs = [];
        $index = 1;

        foreach ($bookingProduct->getBooking()->getGuests() as $guest) {
            if ($guest->getBookingProduct() === $bookingProduct) {
                $resGuestRPHs[] = $index;
            }

            $index++;
        }

        return [
            'RoomTypes'         => [
                'RoomType' => [
                    [
                        'RoomTypeCode'  => $bookingProduct->getProduct()->getIdentifier(),
                        'NumberOfUnits' => 1,
                    ],
                ],
            ],
            'RoomRates'         => [
                'RoomRate' => [
                    'RatePlanCode'  => RatePlanCode::SBX,
                    'RoomTypeCode'  => $bookingProduct->getProduct()->getIdentifier(),
                    'Rates'         => $rates,
                    'NumberOfUnits' => 1,
                ],
            ],
            'Total'             => [
                'AmountAfterTax' => $bookingProduct->getAmount(),
                'CurrencyCode'   => $bookingProduct->getCurrency(),
            ],
            'BasicPropertyInfo' => [
                'HotelCode' => $bookingProduct->getProduct()->getPartner()->getIdentifier(),
                'HotelName' => $bookingProduct->getProduct()->getPartner()->getName(),
            ],
            'GuestCounts'       => [
                'GuestCount' => [
                    'AgeQualifyingCode' => OTAAgeFormatter::DEFAULT_AGE_QUALIFYING_CODE,
                    'Count'             => $bookingProduct->getTotalGuests(),
                ],
            ],
            'TimeSpan'          => [
                // FIXME: in case of multiple roomStays we'll need to get timespan from rates
                'Start' => $bookingProduct->getBooking()->getStartDate()->format('Y-m-d'),
                'End'   => $bookingProduct->getBooking()->getEndDate()->format('Y-m-d'),
            ],
            'Guarantee'         => [
                'GuaranteeType' => 'PrePay',
            ],
            'ResGuestRPHs'      => array_map(
                function ($index) {
                    return [
                        'RPH' => (string) $index,
                    ];
                },
                $resGuestRPHs
            ),
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
        return BookingProduct::class === $class;
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
