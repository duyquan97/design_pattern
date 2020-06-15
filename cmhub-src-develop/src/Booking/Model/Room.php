<?php

namespace App\Booking\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Room
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Room
{
    /**
     * @var string
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $id;

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var Guest[]
     *
     * @Assert\Valid()
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must specify at least one guest"
     * )
     */
    private $guests;

    /**
     * @var Rate[]
     *
     * @Assert\Valid()
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must specify at least one daily_rates"
     * )
     */
    private $dailyRates;

    /**
     * @var null|float
     */
    private $totalAmount;

    /**
     * @var null|string
     *
     */
    private $currency;

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
     * @return Room
     */
    public function setId(string $id): Room
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     *
     * @return Room
     */
    public function setName(?string $name): Room
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|Guest[]
     */
    public function getGuests(): ?array
    {
        return $this->guests;
    }

    /**
     * @param Guest[] $guests
     *
     * @return Room
     */
    public function setGuests(array $guests): Room
    {
        $this->guests = $guests;

        return $this;
    }

    /**
     * @return null|Rate[]
     */
    public function getDailyRates(): ?array
    {
        return $this->dailyRates;
    }

    /**
     * @param Rate[] $dailyRates
     *
     * @return Room
     */
    public function setDailyRates(array $dailyRates): Room
    {
        $this->dailyRates = $dailyRates;

        return $this;
    }

    /**
     * @return null|float
     */
    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    /**
     * @param float $totalAmount
     *
     * @return Room
     */
    public function setTotalAmount(?float $totalAmount): Room
    {
        $this->totalAmount = $totalAmount;

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
     * @return Room
     */
    public function setCurrency(?string $currency): Room
    {
        $this->currency = $currency;

        return $this;
    }
}
