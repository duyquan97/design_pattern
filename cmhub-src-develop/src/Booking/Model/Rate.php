<?php

namespace App\Booking\Model;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Rate
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Rate
{
    /**
     * @var DateTimeInterface
     *
     * @Assert\Date()
     */
    private $date;

    /**
     * @var string
     *
     * @Assert\PositiveOrZero()
     */
    private $price;

    /**
     * @var null|string
     */
    private $currency;

    /**
     * @return null|DateTimeInterface
     */
    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @param DateTimeInterface $date
     *
     * @return Rate
     */
    public function setDate(DateTimeInterface $date): Rate
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * @param string $price
     *
     * @return Rate
     */
    public function setPrice(string $price): Rate
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return Rate
     */
    public function setCurrency(?string $currency): Rate
    {
        $this->currency = $currency;

        return $this;
    }
}
