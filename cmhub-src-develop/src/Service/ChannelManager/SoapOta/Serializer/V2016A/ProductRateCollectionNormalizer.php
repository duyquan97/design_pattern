<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2016A;

use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\ProductRateCollection;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;
use App\Utils\Util;
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
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * @var ProductRateNormalizer
     */
    private $productRateNormalizer;

    /**
     * ProductRateCollectionNormalizer constructor.
     *
     * @param ProductRateCollectionFactory $productRateCollectionFactory
     * @param ProductLoader                $productLoader
     * @param ProductRateNormalizer        $productRateNormalizer
     */
    public function __construct(ProductRateCollectionFactory $productRateCollectionFactory, ProductLoader $productLoader, ProductRateNormalizer $productRateNormalizer)
    {
        $this->productRateCollectionFactory = $productRateCollectionFactory;
        $this->productLoader = $productLoader;
        $this->productRateNormalizer = $productRateNormalizer;
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
     * @param mixed $rateAmountMessages
     * @param array $context
     *
     * @return ProductRateCollection
     *
     * @throws DateFormatException
     * @throws ProductNotFoundException
     * @throws ValidationException
     */
    public function denormalize($rateAmountMessages, array $context = array())
    {
        $productRateCollection = $this->productRateCollectionFactory->create($partner = $context['partner']);

        foreach ($rateAmountMessages as $rateAmountMessage) {
            $product = $this->productLoader->find($partner, $productCode = $rateAmountMessage->StatusApplicationControl->InvTypeCode);
            if (!$product) {
                throw new ProductNotFoundException($partner, $productCode);
            }

            $startDate = \DateTime::createFromFormat('Y-m-d', $rateAmountMessage->StatusApplicationControl->Start);
            $endDate = \DateTime::createFromFormat('Y-m-d', $rateAmountMessage->StatusApplicationControl->End);
            if ($startDate > $endDate) {
                throw new ValidationException("Start date cannot be greater than end date");
            }
            if (!$startDate || !$endDate) {
                throw new DateFormatException('Y-m-d');
            }

            $rates = Util::toArray($rateAmountMessage->Rates->Rate);

            $productRateCollection->addEnabledWeekDays($rateAmountMessage->StatusApplicationControl);

            $productRateCollection->addProductRate($this->productRateNormalizer->denormalize($rates, ['product'   => $product,
                                                                                                      'startDate' => $startDate,
                                                                                                      'endDate'   => $endDate,
            ]));
            $productRateCollection->setEnabledWeekDays([]);
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
