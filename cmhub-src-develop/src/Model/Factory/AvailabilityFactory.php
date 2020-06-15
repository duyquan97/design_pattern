<?php

namespace App\Model\Factory;

use App\Model\Availability;
use App\Model\ProductInterface;

/**
 * Class AvailabilityFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityFactory
{
    /**
     *
     * @param \DateTime        $start
     * @param \DateTime        $end
     * @param int|null         $stock
     * @param ProductInterface $product
     * @param bool             $stopSale
     *
     * @return Availability
     */
    public function create(\DateTime $start, \DateTime $end, ?int $stock, ProductInterface $product, bool $stopSale = null)
    {

        $availability = (new Availability($product))
            ->setStart($start->setTime(0, 0, 0))
            ->setEnd($end->setTime(0, 0, 0))
            ->setStock($stock);

        if (null !== $stopSale) {
            $availability->setStopSale($stopSale);
        }

        return $availability;
    }
}
