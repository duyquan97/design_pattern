<?php

namespace App\Entity\Factory;

use App\Entity\Experience;

/**
 * Class ExperienceFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ExperienceFactory
{
    /**
     *
     * @param string $identifier
     *
     * @return Experience
     */
    public function create(string $identifier)
    {
        return (new Experience())
            ->setIdentifier($identifier);
    }
}
