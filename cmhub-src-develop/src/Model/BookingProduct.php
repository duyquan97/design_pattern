<?php

namespace App\Model;

/**
 * Class BookingProduct
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProduct implements BookingProductInterface
{
    /**
     *
     * @var float
     */
    protected $totalAmount = 0;

    /**
     *
     * @var string
     */
    protected $currency;

    /**
     *
     * @var GuestInterface[]
     */
    protected $guests;

    /**
     *
     * @var RateInterface[]
     */
    protected $rates;

    /**
     *
     * @var BookingInterface
     */
    protected $booking;

    /**
     *
     * @var ProductInterface
     */
    protected $product;

    /**
     * BookingProduct constructor.
     *
     * @param ProductInterface $product
     */
    public function __construct(ProductInterface $product = null)
    {
        $this->product = $product;
        $this->rates = [];
        $this->guests = [];
    }

    /**
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     *
     * @param float $amount
     *
     * @return BookingProduct
     */
    public function setAmount(float $amount): BookingProductInterface
    {
        $this->totalAmount = $amount;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     *
     * @param string $currency
     *
     * @return BookingProduct
     */
    public function setCurrency(string $currency): BookingProductInterface
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     *
     * @return GuestInterface[]
     */
    public function getGuests(): array
    {
        return $this->guests;
    }

    /**
     *
     * @param GuestInterface[] $guests
     *
     * @return BookingProduct
     */
    public function setGuests(array $guests): BookingProductInterface
    {
        $this->guests = $guests;

        return $this;
    }

    /**
     *
     * @param GuestInterface $guest
     *
     * @return BookingProduct
     */
    public function addGuest(GuestInterface $guest): BookingProductInterface
    {
        $this->guests[] = $guest;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getTotalGuests()
    {
        return count($this->getGuests());
    }

    /**
     *
     * @return RateInterface[]
     */
    public function getRates(): array
    {
        return $this->rates;
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
     * @param RateInterface[] $rates
     *
     * @return BookingProduct
     */
    public function setRates(array $rates): BookingProductInterface
    {
        $this->rates = $rates;

        return $this;
    }

    /**
     *
     * @param RateInterface $rate
     *
     * @return BookingProduct
     */
    public function addRate(RateInterface $rate): BookingProductInterface
    {
        $this->rates[] = $rate;

        return $this;
    }

    /**
     *
     * @return BookingInterface
     */
    public function getBooking(): ?BookingInterface
    {
        return $this->booking;
    }

    /**
     *
     * @param BookingInterface $booking
     *
     * @return BookingProduct
     */
    public function setBooking(BookingInterface $booking): BookingProductInterface
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     *
     * @return ProductInterface
     */
    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    /**
     *
     * @param ProductInterface $product
     *
     * @return BookingProduct
     */
    public function setProduct(ProductInterface $product): BookingProductInterface
    {
        $this->product = $product;

        return $this;
    }
}
