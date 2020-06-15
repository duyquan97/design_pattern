<?php

namespace App\Entity\Factory;

use App\Entity\Partner;

/**
 * Class PartnerFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerFactory implements EntityFactoryInterface
{
    /**
     *
     * @return Partner
     */
    public function create()
    {
        return new Partner();
    }
}
