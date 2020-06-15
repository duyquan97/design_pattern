<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Model\PushBooking;
use App\Model\Rate;
use App\Model\RatePlanCode;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class PushBookingNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PushBookingNormalizer implements NormalizerInterface
{
    const DEFAULT_REQUESTOR_ID_TYPE = 22;

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
     * @param PushBooking $pushBooking
     * @param array       $context
     *
     * @return array
     */
    public function normalize($pushBooking, array $context = array())
    {
        return [
            'EchoToken'         => $context['token'],
            'ResStatus'         => $pushBooking->getBooking()->getStatus(),
            'POS'               => [
                'Source' => [
                    'RequestorID'    => [
                        'Type' => self::DEFAULT_REQUESTOR_ID_TYPE,
                        'ID'   => 'SBX',
                    ],
                    'BookingChannel' => [
                        'Primary'     => true,
                        'CompanyName' => [
                            'Code' => RatePlanCode::SBX,
                            '_'    => Rate::SBX_RATE_PLAN_NAME,
                        ],
                    ],
                ],
            ],
            'Inventories'       => [
                'HotelCode' => $pushBooking->getBooking()->getPartner()->getIdentifier(),
            ],
            'HotelReservations' => [
                'HotelReservation' => $this->bookingNormalizer->normalize($pushBooking->getBooking()),
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
        return PushBooking::class === $class;
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
