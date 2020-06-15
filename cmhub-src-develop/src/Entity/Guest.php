<?php

namespace App\Entity;

use App\Model\GuestInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Guest
 *
 * @ORM\Table(name="guests")
 *
 * @ORM\Entity(repositoryClass="App\Repository\GuestRepository")
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Guest implements GuestInterface
{
    /**
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var BookingProduct
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BookingProduct", inversedBy="guests")
     * @ORM\JoinColumn(nullable=true,                                 onDelete="CASCADE")
     */
    protected $bookingProduct;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $surname;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $phone;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $address;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $city;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $zipCode;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $state;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $country;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $countryCode;

    /**
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $age;

    /**
     *
     * @var bool
     *
     * @ORM\Column(name="is_main", type="boolean")
     */
    protected $isMain;

    /**
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * Guest constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     *
     * @return BookingProduct
     */
    public function getBookingProduct(): BookingProduct
    {
        return $this->bookingProduct;
    }

    /**
     *
     * @param BookingProduct $bookingProduct
     *
     * @return Guest
     */
    public function setBookingProduct(BookingProduct $bookingProduct): Guest
    {
        $this->bookingProduct = $bookingProduct;

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
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     *
     * @param string|null $email
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
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     *
     * @param string|null $phone
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
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     *
     * @param string|null $address
     *
     * @return Guest
     */
    public function setAddress(?string $address): Guest
    {
        $this->address = $address;

        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     *
     * @param string|null $city
     *
     * @return Guest
     */
    public function setCity(?string $city): Guest
    {
        $this->city = $city;

        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    /**
     *
     * @param string|null $zipCode
     *
     * @return Guest
     */
    public function setZipCode(?string $zipCode): Guest
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     *
     * @param string|null $state
     *
     * @return Guest
     */
    public function setState(?string $state): Guest
    {
        $this->state = $state;

        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     *
     * @param string|null $country
     *
     * @return Guest
     */
    public function setCountry(?string $country): Guest
    {
        $this->country = $country;

        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     *
     * @param string|null $countryCode
     *
     * @return Guest
     */
    public function setCountryCode(?string $countryCode): Guest
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     *
     * @return int|null
     */
    public function getAge(): ?int
    {
        return $this->age;
    }

    /**
     *
     * @param int|null $age
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
    public function setIsMain(bool $isMain): Guest
    {
        $this->isMain = $isMain;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     *
     * @param \DateTime $createdAt
     *
     * @return Guest
     */
    public function setCreatedAt(\DateTime $createdAt): Guest
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->zipCode;
    }

    /**
     *
     * @return string
     */
    public function getFullName()
    {
        return sprintf('%s %s', $this->getName(), $this->getSurname());
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFullName();
    }
}
