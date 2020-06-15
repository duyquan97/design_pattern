<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Model\ProductCollection;
use App\Model\Rate;
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
        $data = [
            'RoomStays' => [
                [
                    'BasicPropertyInfo' => [
                        'HotelCode' => $object->getPartner()->getIdentifier(),
                    ],
                ],
                'RoomStay' => [],
            ],
        ];

        if (!$object->isEmpty()) {
            foreach ($object->getProducts() as $product) {
                $data['RoomStays']['RoomStay'][] = [
                    'RatePlans' => [
                        'RatePlan' => [
                            'RatePlanCode'        => RatePlanCode::SBX,
                            'RatePlanDescription' => [
                                'Name' => Rate::SBX_RATE_PLAN_NAME,
                            ],
                        ],
                    ],
                    'RoomTypes' => [
                        [
                            'RoomTypeCode'    => $product->getIdentifier(),
                            'RoomDescription' => [
                                'Name' => $product->getName(),
                            ],
                        ],
                    ],
                ];
            }
        }

        return $data;
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
