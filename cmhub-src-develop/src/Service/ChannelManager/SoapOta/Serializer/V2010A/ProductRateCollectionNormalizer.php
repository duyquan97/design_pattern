<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2010A;

use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\ProductRateCollection;
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
     * @return mixed|void
     */
    public function normalize($object, array $context = array())
    {
        throw new MethodNotImplementedException('Method ProductRateCollection::normalize is not implemented');
    }

    /**
     *
     * @param mixed $rateAmountMessages
     * @param array $context
     *
     * @return ProductRateCollection
     *
     * @throws ProductNotFoundException
     * @throws DateFormatException
     * @throws ValidationException
     */
    public function denormalize($rateAmountMessages, array $context = array())
    {
        $productRateCollection = $this->productRateCollectionFactory->create($context['partner']);

        foreach ($rateAmountMessages as $rateAmountMessage) {
            $productRateCollection->addProductRate($this->productRateNormalizer->denormalize($rateAmountMessage, $context));
        }

        return $productRateCollection;
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
