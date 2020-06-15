<?php

namespace App\Application\Sonata\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ApplicationSonataUserBundle
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ApplicationSonataUserBundle extends Bundle
{
    /**
     *
     * @return string
     */
    public function getParent()
    {
        return 'SonataUserBundle';
    }
}
