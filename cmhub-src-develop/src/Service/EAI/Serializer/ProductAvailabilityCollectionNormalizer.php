<?php

namespace App\Service\EAI\Serializer;

use App\Model\ProductAvailabilityCollection;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class ProductAvailabilityCollectionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailabilityCollectionNormalizer implements NormalizerInterface
{

    /**
     *
     * @var ProductAvailabilityNormalizer
     */
    private $productAvailabilityNormalizer;

    /**
     * ProductAvailabilityCollectionNormalizer constructor.
     *
     * @param ProductAvailabilityNormalizer $productAvailabilityNormalizer
     */
    public function __construct(ProductAvailabilityNormalizer $productAvailabilityNormalizer)
    {
        $this->productAvailabilityNormalizer = $productAvailabilityNormalizer;
    }

    /**
     *
     * @param ProductAvailabilityCollectionInterface $object
     * @param array                                  $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        $data = [];

        foreach ($object->getProductAvailabilities() as $productAvailability) {
            $data = array_merge($data, $this->productAvailabilityNormalizer->normalize($productAvailability));
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
        return ProductAvailabilityCollection::class === $class;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return ProductAvailabilityCollection::class === $class;
    }
}
