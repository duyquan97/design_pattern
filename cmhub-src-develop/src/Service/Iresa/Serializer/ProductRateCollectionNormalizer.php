<?php

namespace App\Service\Iresa\Serializer;

use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\ProductRateCollection;
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
     * @var ProductRateCollectionFactory
     */
    private $productRateCollectionFactory;

    /**
     *
     * @var ProductRateNormalizer
     */
    private $productRateNormalizer;

    /**
     * ProductRateCollectionNormalizer constructor.
     *
     * @param ProductRateCollectionFactory $productRateCollectionFactory
     * @param ProductRateNormalizer        $productRateNormalizer
     */
    public function __construct(ProductRateCollectionFactory $productRateCollectionFactory, ProductRateNormalizer $productRateNormalizer)
    {
        $this->productRateCollectionFactory = $productRateCollectionFactory;
        $this->productRateNormalizer = $productRateNormalizer;
    }

    /**
     *
     * @param mixed $object
     * @param array $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        // TODO: Implement normalize() method.
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
        $partner = $context['partner'];
        $rateCollection = $this->productRateCollectionFactory->create($partner);

        foreach ($data as $roomTypeRate) {
            if ($productRate = $this->productRateNormalizer->denormalize($roomTypeRate, ['partner' => $partner])) {
                $rateCollection->addProductRate($productRate);
            }
        }

        return $rateCollection;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
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
        return ProductRateCollection::class === $class;
    }
}
