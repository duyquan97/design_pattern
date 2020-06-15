<?php

namespace App\Booking\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Experience
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Experience
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var ExperienceComponent[]
     *
     * @Assert\Valid()
     */
    private $components = [];

    /**
     * @var float
     *
     * @Assert\PositiveOrZero()
     */
    private $price;

    /**
     * @return null|string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Experience
     */
    public function setId(?string $id): Experience
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return null|ExperienceComponent[]
     */
    public function getComponents(): ?array
    {
        return $this->components;
    }

    /**
     * @param ExperienceComponent[] $components
     *
     * @return Experience
     */
    public function setComponents(array $components): Experience
    {
        $this->components = $components;

        return $this;
    }

    /**
     * @return null|float
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return Experience
     */
    public function setPrice(float $price): Experience
    {
        $this->price = $price;

        return $this;
    }
}
