<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityCollection;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class ProductAvailabilityNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailabilityNormalizer implements NormalizerInterface
{
    /**
     *
     * @param ProductAvailabilityCollection $object
     * @param array                         $context
     *
     * @return array
     */
    public function normalize($object, array $context = array())
    {
        $data = [
            'Inventories' => [
                'HotelCode' => $object->getPartner()->getIdentifier(),
                'Inventory' => [],
            ],
        ];

        /* @var ProductAvailability $productAvailability */
        foreach ($object as $productAvailability) {
            foreach ($productAvailability->getAvailabilities() as $availability) {
                $count = !$availability->isStopSale() ? $availability->getStock() : 0;
                $inventory = [
                    'StatusApplicationControl' => [
                        'Start' => $availability->getStart()->format('Y-m-d'),
                        'End' => $availability->getEnd()->format('Y-m-d'),
                        'InvTypeCode' => $availability->getProduct()->getIdentifier(),
                        'RatePlanCode' => 'SBX',
                        'IsRoom' => 'true',
                    ],
                    'InvCounts' => [
                        [
                            'CountType' => 2,
                            'Count' => (string) $count,
                        ],
                    ],
                ];

                $data['Inventories']['Inventory'][] = $inventory;
            }
        }

        return $data;
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return void
     *
     * @throws \Exception
     */
    public function denormalize($data, array $context = array())
    {
        throw new MethodNotImplementedException('Method ProductAvailability::denormalize is not implemented');
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        if (ProductAvailabilityCollection::class === $class) {
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
