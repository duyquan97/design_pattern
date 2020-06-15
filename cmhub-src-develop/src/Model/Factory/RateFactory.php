<?php

namespace App\Model\Factory;

use App\Model\ProductInterface;
use App\Model\Rate;

/**
 * Class RateFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RateFactory
{
    /**
     *
     * @param \DateTime        $start
     * @param \DateTime        $end
     * @param float            $amount
     * @param ProductInterface $product
     *
     * @return Rate
     */
    public function create(\DateTime $start, \DateTime $end, float $amount, ProductInterface $product): Rate
    {
        return (new Rate())
            ->setStart($start->setTime(0, 0))
            ->setEnd($end->setTime(0, 0))
            ->setAmount($amount)
            ->setProduct($product);
    }
}
