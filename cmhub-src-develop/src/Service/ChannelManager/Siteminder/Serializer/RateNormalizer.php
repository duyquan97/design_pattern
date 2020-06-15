<?php

namespace App\Service\ChannelManager\Siteminder\Serializer;

use App\Model\RateInterface;
use App\Service\ChannelManager\SoapOta\Serializer\V2007B\RateNormalizer as GenericRateNormalizer;

/**
 * Class RateNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RateNormalizer extends GenericRateNormalizer
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
            return [$this->buildRate($dateStart, $dateEnd->modify('+1 day'), $rate->getAmount(), $currencyCode)];
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
}
