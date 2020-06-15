<?php

namespace App\Booking\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ExperienceComponent
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ExperienceComponent
{
    /**
     * @var string
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ExperienceComponent
     */
    public function setName(string $name): ExperienceComponent
    {
        $this->name = $name;

        return $this;
    }
}
