<?php

namespace App\Service\EAI\Serializer;

use App\Model\ProductRateCollection;
use App\Model\ProductRateCollectionInterface;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class ProductRateCollectionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateCollectionNormalizer implements NormalizerInterface
{
    /**
     *
     * @var ProductRateNormalizer
     */
    private $productRateNormalizer;

    /**
     * ProductAvailabilityCollectionNormalizer constructor.
     *
     * @param ProductRateNormalizer $productRateNormalizer
     */
    public function __construct(ProductRateNormalizer $productRateNormalizer)
    {
        $this->productRateNormalizer = $productRateNormalizer;
    }

    /**
     *
     * @param ProductRateCollectionInterface $object
     * @param array                          $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        $data = [];

        foreach ($object->getProductRates() as $productRate) {
            $data = array_merge($data, $this->productRateNormalizer->normalize($productRate));
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
        return ProductRateCollection::class === $class;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return ProductRateCollection::class === $class;
    }
}
