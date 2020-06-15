<?php

namespace App\Entity;

use App\Model\BookingProductInterface;
use App\Model\GuestInterface;
use App\Model\ProductInterface;
use App\Model\RateInterface;
use App\Utils\Monolog\LoggableInterface;
use App\Utils\Monolog\LogKey;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * BookingProduct
 *
 * @ORM\Table(name="booking_products")
 * @ORM\Entity(repositoryClass="App\Repository\BookingProductRepository")
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProduct implements BookingProductInterface, LoggableInterface
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
     *
     * @var float
     *
     * @ORM\Column(type="float")
     */
    protected $totalAmount = 0;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", length=3)
     */
    protected $currency;

    /**
     *
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    protected $product;

    /**
     *
     * @var Booking
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Booking", inversedBy="bookingProducts", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    protected $booking;

    /**
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Guest", mappedBy="bookingProduct", cascade={"persist", "remove"})
     */
    protected $guests;

    /**
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="BookingProductRate", mappedBy="bookingProduct", cascade={"persist", "remove"})
     */
    protected $rates;

    /**
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * BookingProduct constructor.
     */
    public function __construct()
    {
        $this->rates = new ArrayCollection();
        $this->guests = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    /**
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     *
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     *
     * @param float $totalAmount
     *
     * @return BookingProduct
     */
    public function setTotalAmount(float $totalAmount): BookingProduct
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     *
     * @param string $currency
     *
     * @return BookingProduct
     */
    public function setCurrency(string $currency): BookingProduct
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     *
     * @param ProductInterface $product
     *
     * @return BookingProduct
     */
    public function setProduct(ProductInterface $product): BookingProduct
    {
        $this->product = $product;

        return $this;
    }

    /**
     *
     * @return Booking
     */
    public function getBooking(): Booking
    {
        return $this->booking;
    }

    /**
     *
     * @param Booking $booking
     *
     * @return BookingProduct
     */
    public function setBooking(Booking $booking): BookingProduct
    {
        $this->booking = $booking;

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
     * @return BookingProduct
     */
    public function setCreatedAt(\DateTime $createdAt): BookingProduct
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     *
     * @param iterable $guests
     *
     * @return BookingProduct
     */
    public function setGuests(iterable $guests): BookingProductInterface
    {
        foreach ($guests as $guest) {
            $guest->setBookingProduct($this);
        }

        $this->guests = $guests;

        return $this;
    }

    /**
     *
     * @return GuestInterface[]|ArrayCollection
     */
    public function getGuests()
    {
        return $this->guests;
    }

    /**
     *
     * @return BookingProductRate[]|ArrayCollection
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     *
     * @param BookingProductRate[]|Collection $rates
     *
     * @return BookingProduct
     */
    public function setRates($rates): BookingProductInterface
    {
        $this->rates = new ArrayCollection();

        foreach ($rates as $rate) {
            $rate->setBookingProduct($this);
            $this->rates->add($rate);
        }

        return $this;
    }

    /**
     * Adds a rate.
     *
     * @param RateInterface $rate The rate
     *
     * @return self
     */
    public function addRate(RateInterface $rate): BookingProductInterface
    {
        if (!$this->rates->contains($rate)) {
            $this->rates->add($rate);
        }

        return $this;
    }

    /**
     * Adds a guest.
     *
     * @param GuestInterface $guest The guest
     *
     * @return self
     */
    public function addGuest(GuestInterface $guest): BookingProductInterface
    {
        $guests = $this->getGuests();
        $guests[] = $guest;
        $this->setGuests($guests);

        return $this;
    }

    /**
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->totalAmount;
    }

    /**
     *
     * @param float $amount
     *
     * @return BookingProductInterface|void
     */
    public function setAmount(float $amount)
    {
        $this->totalAmount = $amount;
    }

    /**
     *
     * @param \DateTime $date
     *
     * @return bool
     */
    public function hasRate(\DateTime $date)
    {
        foreach ($this->rates as $rate) {
            if ($rate->getStart()->format('Y-m-d') === $date->format('Y-m-d')) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @return int|void
     */
    public function getTotalGuests()
    {
        return count($this->getGuests());
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->getProduct()) {
            return 'EMPTY PRODUCT';
        }

        return sprintf(
            'Product: %s (%s) | Guests: %d',
            $this->getProduct()->getName(),
            $this->getProduct()->getIdentifier(),
            sizeof($this->getGuests())
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            LogKey::INTERNAL_ID    => (string) $this->getId(),
            LogKey::TYPE_KEY       => 'booking_product',
            LogKey::AMOUNT         => (float) $this->getTotalAmount(),
            LogKey::CURRENCY       => $this->getCurrency(),
            LogKey::PRODUCT_ID_KEY => $this->getProduct()->getIdentifier(),
            LogKey::IDENTIFIER_KEY => $this->getBooking()->getReservationId(),
            LogKey::CREATED_AT_KEY => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d H:i:s') : '',

        ];
    }
}
