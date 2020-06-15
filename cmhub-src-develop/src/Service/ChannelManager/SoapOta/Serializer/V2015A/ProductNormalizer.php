<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2015A;

use App\Model\Availability;
use App\Model\ProductCollection;
use App\Model\RatePlanCode;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class ProductNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductNormalizer implements NormalizerInterface
{
    /**
     *
     * @param ProductCollection $object
     * @param array             $context
     *
     * @return array
     */
    public function normalize($object, array $context = array())
    {
        if ('OTA_HotelAvailGetRQ' === $context['targetOperation']) {
            return $this->normalizeHotelAvailGet($object, $context);
        }

        if ('OTA_HotelProductRQ' === $context['targetOperation']) {
            return $this->normalizeHotelProduct($object, $context);
        }

        return [];
    }

    /**
     *
     * @param ProductCollection $object
     * @param array             $context
     *
     * @return array
     */
    public function normalizeHotelAvailGet($object, array $context = array()): array
    {
        $data = [
            'AvailStatusMessages' => [
                'HotelCode' => $object->getPartner()->getIdentifier(),
                'AvailStatusMessage' => [],
            ],
        ];

        if (!$object->isEmpty()) {
            $availabilities = $context['availabilities'];
            foreach ($availabilities->getProductAvailabilities() as $productAvailability) {
                foreach ($productAvailability->getAvailabilities() as $availability) {
                    $data['AvailStatusMessages']['AvailStatusMessage'][] = [
                        'StatusApplicationControl' => [
                            'Start' => $availability->getStart()->format('Y-m-d'),
                            'End' => $availability->getEnd()->format('Y-m-d'),
                            'InvTypeCode' => $availability->getProduct()->getIdentifier(),
                            'RatePlanCode' => RatePlanCode::SBX,
                        ],
                        'RestrictionStatus' => [
                            'Restriction' => 'Master',
                            'Status' => $availability->isStopSale() ? 'Close' : 'Open',
                        ],
                    ];
                }
            }
        }

        return $data;
    }

    /**
     *
     * @param ProductCollection $object
     * @param array             $context
     *
     * @return array
     */
    public function normalizeHotelProduct($object, array $context = array()): array
    {
        $data = [
            'HotelProducts' => [
                [
                    'HotelCode' => $object->getPartner()->getIdentifier(),
                    'HotelProduct' => [],
                ],
            ],
        ];

        if (!$object->isEmpty()) {
            foreach ($object->getProducts() as $product) {
                $data['HotelProducts']['HotelProduct'][] = [
                    'RatePlans' => [
                        'RatePlan' => [
                            'RatePlanCode' => RatePlanCode::SBX,
                        ],
                    ],
                    'RoomTypes' => [
                        [
                            'RoomTypeCode'    => $product->getIdentifier(),
                            'RoomTypeName' => $product->getName(),
                        ],
                    ],
                ];
            }
        }

        return $data;
    }

    /**
     * @param  mixed                        $data    The data
     * @param  array                        $context The context
     *
     * @throws MethodNotImplementedException
     *
     * @return bool
     */
    public function denormalize($data, array $context = array())
    {
        throw new MethodNotImplementedException('Method ProductCollection::denormalize is not implemented');
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        if (ProductCollection::class === $class) {
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
