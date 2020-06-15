<?php

namespace App\Service\Iresa;

/**
 * Class IresaEndpointInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface IresaEndpointInterface
{
    /**
     *
     * @param array $request
     *
     * @return null|array
     */
    public function run(array $request): ?array;
}
