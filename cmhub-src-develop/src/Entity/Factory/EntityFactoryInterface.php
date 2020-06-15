<?php

namespace App\Entity\Factory;

/**
 * Interface EntityFactoryInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface EntityFactoryInterface
{
    /**
     *
     * @return mixed
     */
    public function create();
}
