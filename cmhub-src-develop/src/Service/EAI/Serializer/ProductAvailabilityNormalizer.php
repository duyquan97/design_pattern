<?php

namespace App\Service\EAI\Serializer;

use App\Repository\AvailabilityRepository;
use App\Model\Availability;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityInterface;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class ProductAvailabilityNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailabilityNormalizer implements NormalizerInterface
{

    /**
     * @var AvailabilityRepository
     */
    private $availabilityRepository;

    /**
     * ProductAvailabilityNormalizer constructor.
     *
     * @param AvailabilityRepository $availabilityRepository
     */
    public function __construct(AvailabilityRepository $availabilityRepository)
    {
        $this->availabilityRepository = $availabilityRepository;
    }

    /**
     *
     * @param ProductAvailabilityInterface $object
     * @param array                        $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        $data = [];
        $productId = $object->getProduct()->getIdentifier();
        $partnerId = $object->getProduct()->getPartner()->getIdentifier();

        /** @var Availability $availability */
        foreach ($object->getAvailabilities() as $availability) {
            $data[] = [
                'rateBand'  => [
                    'code'    => 'SBX',
                    'partner' => [
                        'id' => $partnerId,
                    ],
                ],
                'product'   => [
                    'id' => $productId,
                ],
                'dateFrom'  => $availability->getStart()->format('Y-m-d\\TH:i:s.P'),
                'dateTo'    => (clone $availability->getEnd())->modify('+1 day')->format('Y-m-d\\TH:i:s.P'),
                'updatedAt' => date_create()->format('Y-m-d\\TH:i:s.P'),
                'quantity'  => $availability->isStopSale() ? 0 : $availability->getStock(),
            ];
        }

        return $data;
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return mixed
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
        return ProductAvailability::class === $class;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return ProductAvailability::class === $class;
    }
}
