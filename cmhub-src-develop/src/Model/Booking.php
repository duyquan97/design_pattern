<?php

namespace App\Model;

use App\Booking\Model\BookingStatus;
use App\Entity\ChannelManager;
use App\Entity\Experience;
use App\Entity\Partner;
use App\Entity\Transaction;

/**
 * Class Booking
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Booking implements BookingInterface
{
    /**
     *
     * @var string
     */
    protected $status;

    /**
     *
     * @var string
     */
    protected $identifier;

    /**
     * @var Partner
     */
    protected $partner;

    /**
     *
     * @var \DateTime
     */
    protected $startDate;

    /**
     *
     * @var \DateTime
     */
    protected $endDate;

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
     * @var BookingProductInterface[]
     */
    protected $bookingProducts;

    /**
     *
     * @var \DateTime
     */
    protected $createDate;

    /**
     *
     * @var \DateTime
     */
    protected $lastModifyDate;

    /**
     *
     * @var string
     */
    protected $comments;

    /**
     *
     * @var string
     */
    protected $requests;

    /**
     *
     * @var string
     */
    protected $voucherNumber;

    /**
     * @var Experience
     */
    protected $experience;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var bool
     */
    protected $processed = false;

    /**
     * Booking constructor.
     */
    public function __construct()
    {
        $this->bookingProducts = [];
    }

    /**
     *
     * @return array|void
     */
    public function getComponents()
    {
        // TODO: Implement getComponents() method.

        return [];
    }

    /**
     *
     * @param array $components
     *
     * @return BookingInterface|void
     */
    public function setComponents(array $components)
    {
        // TODO: Implement setComponents() method.

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     *
     * @param string $status
     *
     * @return Booking
     */
    public function setStatus(string $status): BookingInterface
    {
        $this->status = $status;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getReservationId(): string
    {
        return $this->identifier;
    }

    /**
     *
     * @param string $reservationId
     *
     * @return Booking
     */
    public function setReservationId(string $reservationId): BookingInterface
    {
        $this->identifier = $reservationId;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    /**
     *
     * @param \DateTime $startDate
     *
     * @return Booking
     */
    public function setStartDate(?\DateTime $startDate): BookingInterface
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     *
     * @param \DateTime $endDate
     *
     * @return Booking
     */
    public function setEndDate(?\DateTime $endDate): BookingInterface
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     *
     * @return float
     */
    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    /**
     *
     * @param float $totalAmount
     *
     * @return Booking
     */
    public function setTotalAmount(float $totalAmount): BookingInterface
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     *
     * @param float $amount
     *
     * @return $this
     */
    public function addTotalAmount(float $amount): BookingInterface
    {
        $this->totalAmount += $amount;

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
     * @return Booking
     */
    public function setCurrency(string $currency): BookingInterface
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createDate;
    }

    /**
     *
     * @param \DateTime $createdAt
     *
     * @return Booking
     */
    public function setCreatedAt(\DateTime $createdAt): Booking
    {
        $this->createDate = $createdAt;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->lastModifyDate;
    }

    /**
     *
     * @param \DateTime $updatedAt
     *
     * @return Booking
     */
    public function setUpdatedAt(\DateTime $updatedAt): Booking
    {
        $this->lastModifyDate = $updatedAt;

        return $this;
    }

    /**
     *
     * @return BookingProductInterface[]
     */
    public function getBookingProducts()
    {
        return $this->bookingProducts;
    }

    /**
     *
     * @param BookingProductInterface[] $bookingProducts
     *
     * @return Booking
     */
    public function setBookingProducts(array $bookingProducts): BookingInterface
    {
        $this->bookingProducts = $bookingProducts;

        return $this;
    }

    /**
     *
     * @param BookingProductInterface $bookingProduct
     *
     * @return Booking
     */
    public function addBookingProduct(BookingProductInterface $bookingProduct): BookingInterface
    {
        $this->bookingProducts[] = $bookingProduct;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     *
     * @param string $comments
     *
     * @return Booking
     */
    public function setComments(?string $comments): BookingInterface
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getVoucherNumber(): ?string
    {
        return $this->voucherNumber;
    }

    /**
     *
     * @param string $voucherNumber
     *
     * @return Booking
     */
    public function setVoucherNumber(string $voucherNumber = null): BookingInterface
    {
        $this->voucherNumber = $voucherNumber;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getRequests(): ?string
    {
        return $this->requests;
    }

    /**
     *
     * @param string $requests
     *
     * @return Booking
     */
    public function setRequests(?string $requests): BookingInterface
    {
        $this->requests = $requests;

        return $this;
    }

    /**
     *
     * @return BookingProductInterface|null
     */
    public function firstBookingProduct(): ?BookingProductInterface
    {
        return empty($this->bookingProducts) ? null : $this->bookingProducts[0];
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
     * @return GuestInterface[]
     */
    public function getGuests(): array
    {
        $guests = [];
        foreach ($this->bookingProducts as $bookingProduct) {
            $guests = array_merge($guests, $bookingProduct->getGuests());
        }

        return $guests;
    }

    /**
     *
     * @return RateInterface[]
     */
    public function getRates(): array
    {
        $rates = [];
        foreach ($this->bookingProducts as $bookingProduct) {
            $rates = array_merge($rates, $bookingProduct->getRates());
        }

        return $rates;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return BookingStatus::CONFIRMED === $this->status;
    }

    /**
     * Get partner
     *
     * @return Partner|null
     */
    public function getPartner(): ?Partner
    {
        return $this->partner;
    }

    /**
     * Set partner
     *
     * @param Partner $partner
     *
     * @return Booking
     */
    public function setPartner(Partner $partner): BookingInterface
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * @return Experience|null
     */
    public function getExperience(): ?ExperienceInterface
    {
        return $this->experience;
    }

    /**
     * @param Experience $experience
     *
     * @return $this
     */
    public function setExperience(Experience $experience): BookingInterface
    {
        $this->experience = $experience;

        return $this;
    }

    /**
     * @return ChannelManager|null
     */
    public function getChannelManager(): ?ChannelManager
    {
        return $this->channelManager;
    }

    /**
     * @param ChannelManager|null $channelManager
     *
     * @return Booking
     */
    public function setChannelManager(?ChannelManager $channelManager): BookingInterface
    {
        $this->channelManager = $channelManager;

        return $this;
    }

    /**
     * @return Transaction|null
     */
    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    /**
     * @param Transaction|null $transaction
     *
     * @return $this
     */
    public function setTransaction(?Transaction $transaction): BookingInterface
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isProcessed(): bool
    {
        return $this->processed;
    }

    /**
     *
     * @param bool $processed
     *
     * @return Booking
     */
    public function setProcessed(bool $processed): BookingInterface
    {
        $this->processed = $processed;

        return $this;
    }
}
