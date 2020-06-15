<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2016A;

use App\Exception\ValidationException;
use App\Model\Factory\ProductRateFactory;
use App\Model\Factory\RateFactory;
use App\Model\ProductRate;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class ProductRateNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateNormalizer implements NormalizerInterface
{
    /**
     * @var ProductRateFactory
     */
    private $productRateFactory;

    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * ProductRateNormalizer constructor.
     *
     * @param ProductRateFactory $productRateFactory
     * @param RateFactory        $rateFactory
     */
    public function __construct(ProductRateFactory $productRateFactory, RateFactory $rateFactory)
    {
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
        throw new MethodNotImplementedException('Method ProductRate::normalize is not implemented');
    }

    /**
     *
     * @param mixed $rates
     * @param array $context
     *
     * @return ProductRate
     *
     * @throws ValidationException
     */
    public function denormalize($rates, array $context = array())
    {
        $productRate = $this->productRateFactory->create($product = $context['product']);
        foreach ($rates as $rate) {
            foreach ($rate->BaseByGuestAmts as $baseByGuestAmt) {
                if (is_array($baseByGuestAmt)) {
                    $baseByGuestAmt = array_filter($baseByGuestAmt, function ($item) {
                        return (int) $item->NumberOfGuests === 2;
                    });

                    $baseByGuestAmt = array_shift($baseByGuestAmt);
                }

                $decimal = intval($baseByGuestAmt->DecimalPlaces);
                if ($decimal < 0) {
                    throw new ValidationException('Decimals cannot be negative');
                }
                $amount = (intval($baseByGuestAmt->AmountAfterTax) / (pow(10, $decimal)));
                if ($amount < 0) {
                    throw new ValidationException('The amount cannot be negative');
                }

                $rate = $this->rateFactory->create(
                    $context['startDate'],
                    $context['endDate'],
                    $amount,
                    $product
                );

                $productRate->addRate($rate);
            }
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
