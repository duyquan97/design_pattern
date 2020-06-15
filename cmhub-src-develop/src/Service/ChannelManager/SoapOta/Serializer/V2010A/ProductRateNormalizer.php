<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2010A;

use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductRateFactory;
use App\Model\ProductRate;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;
use App\Utils\Util;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class ProductRateNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateNormalizer implements NormalizerInterface
{
    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * @var ProductRateFactory
     */
    private $productRateFactory;

    /**
     * @var RateNormalizer
     */
    private $rateNormalizer;

    /**
     * ProductRateNormalizer constructor.
     *
     * @param ProductLoader      $productLoader
     * @param ProductRateFactory $productRateFactory
     * @param RateNormalizer     $rateNormalizer
     */
    public function __construct(ProductLoader $productLoader, ProductRateFactory $productRateFactory, RateNormalizer $rateNormalizer)
    {
        $this->productLoader = $productLoader;
        $this->productRateFactory = $productRateFactory;
        $this->rateNormalizer = $rateNormalizer;
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
        throw new MethodNotImplementedException('Method ProductRate::normalize is not implemented');
    }

    /**
     *
     * @param mixed $rateAmountMessage
     * @param array $context
     *
     * @return ProductRate
     *
     * @throws ProductNotFoundException
     * @throws DateFormatException
     * @throws ValidationException
     */
    public function denormalize($rateAmountMessage, array $context = array())
    {
        $product = $this->productLoader->find($partner = $context['partner'], $productCode = $rateAmountMessage->StatusApplicationControl->InvTypeCode);

        if (!$product) {
            throw new ProductNotFoundException($partner, $productCode);
        }

        $startDate = \DateTime::createFromFormat('Y-m-d', $rateAmountMessage->StatusApplicationControl->Start);
        $endDate = \DateTime::createFromFormat('Y-m-d', $rateAmountMessage->StatusApplicationControl->End);

        if (!$startDate || !$endDate) {
            throw new DateFormatException('Y-m-d');
        }

        if ($startDate > $endDate) {
            throw new ValidationException("Start date cannot be greater than end date");
        }

        $productRate = $this->productRateFactory->create($product);
        $rates = Util::toArray($rateAmountMessage->Rates->Rate);

        foreach ($rates as $rate) {
            $productRate->addRate($this->rateNormalizer->denormalize($rate, ['product'   => $product,
                                                                             'startDate' => $startDate,
                                                                             'endDate'   => $endDate,
            ]));
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
