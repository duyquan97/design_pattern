<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Model\Rate;
use App\Model\RateInterface;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class RateNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RateNormalizer implements NormalizerInterface
{
    /**
     *
     * @param RateInterface $rate    The rate
     * @param array         $context The context
     *
     * @return array
     */
    public function normalize($rate, array $context = array())
    {
        $currencyCode = $rate->getProduct()->getPartner()->getCurrency();
        $dateStart = clone $rate->getStart();
        $dateEnd = clone $rate->getEnd();

        // if same date then build one single element
        if ($dateStart->format('Y-m-d') === $dateEnd->format('Y-m-d')) {
            return [$this->buildRate($dateStart, $dateStart, $rate->getAmount(), $currencyCode)];
        }

        $rates = [];
        while ($dateStart < $dateEnd) {
            $expireDate = clone $dateStart;
            $expireDate->modify('+1 day');

            $rates[] = $this->buildRate($dateStart, $expireDate, $rate->getAmount(), $currencyCode);

            $dateStart->modify('+1 day');
        }

        return $rates;
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return void
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
        return Rate::class === $class;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return false;
    }

    /**
     *
     * @param \DateTime $effectiveDate
     * @param \DateTime $expireDate
     * @param float     $amount
     * @param string    $currency
     *
     * @return array
     */
    protected function buildRate(\DateTime $effectiveDate, \DateTime $expireDate, float $amount, string $currency)
    {
        return [
            'UnitMultiplier' => 1,
            'EffectiveDate'  => $effectiveDate->format('Y-m-d'),
            'ExpireDate'     => $expireDate->format('Y-m-d'),
            'Base'           => [
                'AmountAfterTax' => $amount,
                'CurrencyCode'   => $currency,
            ],
        ];
    }
}
