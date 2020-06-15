<?php

namespace App\Model;

/**
 * Class Guest
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Guest implements GuestInterface
{
    /**
     *
     * @var bool
     */
    protected $isMain = false;

    /**
     * @var BookingProduct
     */
    protected $bookingProduct;

    /**
     *
     * @var int
     */
    protected $age;

    /**
     *
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
     * @var string
     */
    protected $email;

    /**
     *
     * @var string
     */
    protected $phone;

    /**
     *
     * @var string
     */
    protected $country;

    /**
     *
     * @var string
     */
    protected $countryCode;

    /**
     *
     * @var string
     */
    protected $address;

    /**
     *
     * @var string
     */
    protected $city;

    /**
     *
     * @var string
     */
    protected $zipCode;

    /**
     *
     * @var string
     */
    protected $state;

    /**
     * Guest constructor.
     */
    public function __construct()
    {
        $this->age = 0;
        $this->isMain = false;
    }


    /**
     *
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->isMain;
    }

    /**
     *
     * @param bool $isMain
     *
     * @return Guest
     */
    public function setIsMain(bool $isMain): GuestInterface
    {
        $this->isMain = $isMain;

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
    public function setAge(?int $age): GuestInterface
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
    public function setName(?string $name): GuestInterface
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
    public function setSurname(?string $surname): GuestInterface
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
    public function setEmail(?string $email): GuestInterface
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
    public function setPhone(?string $phone): GuestInterface
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     *
     * @param string $country
     *
     * @return Guest
     */
    public function setCountry(?string $country): GuestInterface
    {
        $this->country = $country;

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
    public function setCountryCode(?string $countryCode): GuestInterface
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     *
     * @param string $address
     *
     * @return Guest
     */
    public function setAddress(?string $address): GuestInterface
    {
        $this->address = $address;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     *
     * @param string $city
     *
     * @return Guest
     */
    public function setCity(?string $city): GuestInterface
    {
        $this->city = $city;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getPostalCode(): ?string
    {
        return $this->zipCode;
    }

    /**
     *
     * @param string $postalCode
     *
     * @return Guest
     */
    public function setPostalCode(?string $postalCode): GuestInterface
    {
        $this->zipCode = $postalCode;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     *
     * @param string $state
     *
     * @return Guest
     */
    public function setState(?string $state): GuestInterface
    {
        $this->state = $state;

        return $this;
    }

    /**
     *
     * @return BookingProduct
     */
    public function getBookingProduct(): BookingProductInterface
    {
        return $this->bookingProduct;
    }

    /**
     *
     * @param BookingProductInterface $bookingProduct
     *
     * @return Guest
     */
    public function setBookingProduct(BookingProductInterface $bookingProduct): GuestInterface
    {
        $this->bookingProduct = $bookingProduct;

        return $this;
    }
}
