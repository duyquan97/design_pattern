<?php

namespace App\Booking\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Guest
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Guest
{
    /**
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $surname;

    /**
     *
     * @var string|null
     */
    protected $email;

    /**
     *
     * @var string|null
     *
     */
    protected $phone;

    /**
     *
     * @var int|null
     *
     * @Assert\PositiveOrZero()
     */
    protected $age;

    /**
     * @var bool
     *
     * @Assert\NotNull()
     */
    protected $main = false;

    /**
     *
     * @var string|null
     */
    protected $countryCode;

    /**
     *
     *
     * @var string|null
     *
     */
    protected $country;

    /**
     *
     * @var string|null
     *
     */
    protected $address;

    /**
     *
     * @var string|null
     *
     */
    protected $city;

    /**
     *
     * @var string|null
     *
     */
    protected $zipCode;

    /**
     *
     * @var string|null
     *
     */
    protected $state;

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->main;
    }

    /**
     *
     * @param bool $main
     *
     * @return Guest
     */
    public function setMain(bool $main): Guest
    {
        $this->main = $main;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getAge(): ?int
    {
        return $this->age;
    }

    /**
     *
     * @param int $age
     *
     * @return Guest
     */
    public function setAge(?int $age): Guest
    {
        $this->age = $age;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     *
     * @param string $name
     *
     * @return Guest
     */
    public function setName(?string $name): Guest
    {
        $this->name = $name;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     *
     * @param string $surname
     *
     * @return Guest
     */
    public function setSurname(?string $surname): Guest
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     *
     * @param string $email
     *
     * @return Guest
     */
    public function setEmail(?string $email): Guest
    {
        $this->email = $email;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     *
     * @param string $phone
     *
     * @return Guest
     */
    public function setPhone(?string $phone): Guest
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     *
     * @param string $countryCode
     *
     * @return Guest
     */
    public function setCountryCode(?string $countryCode): Guest
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return Guest
     */
    public function setCountry(?string $country): Guest
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string $address
     *
     * @return Guest
     */
    public function setAddress(?string $address): Guest
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Guest
     */
    public function setCity(?string $city): Guest
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     *
     * @return Guest
     */
    public function setZipCode(?string $zipCode): Guest
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return Guest
     */
    public function setState(?string $state): Guest
    {
        $this->state = $state;

        return $this;
    }
}
