<?php

namespace App\Service\Iresa\Serializer;

use App\Model\Factory\ProductRateFactory;
use App\Model\Factory\RateFactory;
use App\Model\ProductRate;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class ProductRateNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateNormalizer implements NormalizerInterface
{
    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     *
     * @var ProductRateFactory
     */
    private $productRateFactory;

    /**
     *
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * ProductRateNormalizer constructor.
     *
     * @param ProductLoader      $productLoader
     * @param ProductRateFactory $productRateFactory
     * @param RateFactory        $rateFactory
     */
    public function __construct(ProductLoader $productLoader, ProductRateFactory $productRateFactory, RateFactory $rateFactory)
    {
        $this->productLoader = $productLoader;
        $this->productRateFactory = $productRateFactory;
        $this->rateFactory = $rateFactory;
    }

    /**
     *
     * @param mixed $object
     * @param array $context
     *
     * @return void
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
     * @return ProductRate|null
     */
    public function denormalize($data, array $context = array())
    {
        if (!isset($data->roomTypeCode) || !$roomTypeCode = $data->roomTypeCode) {
            return null;
        }

        $product = $this->productLoader->find($context['partner'], $roomTypeCode);
        if (!$product) {
            // TODO: Log the product doesn't exist in DB but exists in Iresa
            return null;
        }

        $productRate = $this->productRateFactory->create($product);
        foreach ($data->rates as $rate) {
            $rateModel = $this
                ->rateFactory
                ->create(
                    new \DateTime($rate->date),
                    new \DateTime($rate->date),
                    isset($rate->amount) ? $rate->amount : 0,
                    $product
                );

            $productRate->addRate($rateModel);
        }

        return $productRate;
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
        return ProductRate::class === $class;
    }
}
