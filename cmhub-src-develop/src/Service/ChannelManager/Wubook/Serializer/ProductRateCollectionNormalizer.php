<?php

namespace App\Service\ChannelManager\Wubook\Serializer;

use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\RatePlanNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\ProductRateCollection;
use App\Model\RatePlanCode;
use App\Model\WubookErrorCode;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class ProductRateCollectionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateCollectionNormalizer implements NormalizerInterface
{
    /**
     * @var ProductRateCollectionFactory
     */
    private $productRateCollectionFactory;

    /**
     * @var RateNormalizer
     */
    private $rateNormalizer;

    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * ProductRateCollectionNormalizer constructor.
     *
     * @param ProductRateCollectionFactory $productRateCollectionFactory
     * @param RateNormalizer               $rateNormalizer
     * @param ProductLoader                $productLoader
     */
    public function __construct(ProductRateCollectionFactory $productRateCollectionFactory, RateNormalizer $rateNormalizer, ProductLoader $productLoader)
    {
        $this->productRateCollectionFactory = $productRateCollectionFactory;
        $this->rateNormalizer = $rateNormalizer;
        $this->productLoader = $productLoader;
    }

    /**
     *
     * @param mixed $rates
     * @param array $context
     *
     * @return ProductRateCollection
     *
     * @throws DateFormatException
     * @throws ProductNotFoundException
     * @throws ValidationException
     * @throws RatePlanNotFoundException
     */
    public function denormalize($rates, array $context = array())
    {
        $collection = $this->productRateCollectionFactory->create($partner = $context['partner']);

        if (empty($rates->prices)) {
            return $collection;
        }

        foreach ($rates->prices as $rate) {
            if (!isset($rate->rate_id)) {
                throw new ValidationException("Rate plan is not defined");
            }

            if (!isset($rate->room_id)) {
                throw new ValidationException("Room id is not defined");
            }

            if (!in_array($rate->rate_id, RatePlanCode::RATE_PLAN_CODES)) {
                throw new RatePlanNotFoundException($rate->rate_id);
            }


            $product = $this->productLoader->find($partner, $roomId = $rate->room_id);

            if (!$product) {
                throw new ProductNotFoundException($partner, $roomId, WubookErrorCode::PRODUCT_NOT_FOUND);
            }

            $rateObject = $this->rateNormalizer->denormalize($rate, ['product' => $product]);
            $collection->addRate($product, $rateObject);
        }

        return $collection;
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
        throw new MethodNotImplementedException('Method ProductRateCollection::normalize is not implemented');
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
