<?php

namespace App\Entity;

use App\Model\BookingInterface;
use App\Model\BookingProductInterface;
use App\Booking\Model\BookingStatus;
use App\Model\GuestInterface;
use App\Utils\Monolog\LoggableInterface;
use App\Utils\Monolog\LogKey;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Booking
 *
 * @ORM\Table(name="bookings")
 * @ORM\Entity(repositoryClass="App\Repository\BookingRepository")
 * @ORM\EntityListeners({"App\Repository\Listener\BookingListener"})
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Booking implements BookingInterface, LoggableInterface
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
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $identifier = '';

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Partner", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $partner;

    /**
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\BookingProduct", mappedBy="booking", cascade={"persist", "remove"})
     */
    private $bookingProducts;

    /**
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="create_date")
     */
    private $createdAt;

    /**
     *
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(type="datetime", name="last_modify_date")
     */
    private $updatedAt;

    /**
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    private $startDate;

    /**
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    private $endDate;

    /**
     *
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $totalAmount = 0;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", length=3)
     */
    private $currency;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $requests;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     *
     * @var Transaction
     *
     * @ORM\OneToOne(targetEntity="Transaction", cascade={"persist"})
     * @ORM\JoinColumn(name="transaction_id", nullable=true, onDelete="CASCADE")
     */
    private $transaction;

    /**
     *
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $voucherNumber;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Experience", cascade={"persist"})
     * @ORM\JoinColumn(name="experience_id", nullable=true, onDelete="SET NULL")
     */
    private $experience;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ChannelManager")
     * @ORM\JoinColumn(name="channel_manager_id", nullable=true)
     */
    private $channelManager;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $components = [];

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_processed", options={"default": "0"})
     */
    private $processed = false;

    /**
     * Booking constructor.
     */
    public function __construct()
    {
        $this->bookingProducts = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     *
     * @param string $identifier
     *
     * @return Booking
     */
    public function setIdentifier(string $identifier): Booking
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     *
     * @param mixed $partner
     *
     * @return Booking
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;

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
     * @return Booking
     */
    public function setCreatedAt(\DateTime $createdAt): Booking
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     *
     * @param null|\DateTime $updatedAt
     *
     * @return Booking
     */
    public function setUpdatedAt(?\DateTime $updatedAt): Booking
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    /**
     *
     * @param \DateTime $startDate
     *
     * @return Booking
     */
    public function setStartDate(\DateTime $startDate): Booking
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    /**
     *
     * @param \DateTime $endDate
     *
     * @return Booking
     */
    public function setEndDate(\DateTime $endDate): Booking
    {
        $this->endDate = $endDate;

        return $this;
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
     * @return Booking
     */
    public function setTotalAmount(float $totalAmount): Booking
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
     * @return Booking
     */
    public function setCurrency(string $currency): Booking
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     *
     * @param string $status
     *
     * @return Booking
     */
    public function setStatus(string $status): Booking
    {
        $this->status = $status;

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
    public function setRequests(string $requests): Booking
    {
        $this->requests = $requests;

        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     *
     * @param string|null $comments
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
     * @return null|Transaction
     */
    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    /**
     *
     * @param Transaction|null $transaction
     *
     * @return Booking
     */
    public function setTransaction(?Transaction $transaction): Booking
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getVoucherNumber(): ?string
    {
        return $this->voucherNumber;
    }

    /**
     *
     * @param string|null $voucherNumber
     *
     * @return Booking
     */
    public function setVoucherNumber(?string $voucherNumber): Booking
    {
        $this->voucherNumber = $voucherNumber;

        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getExperience()
    {
        return $this->experience;
    }

    /**
     *
     * @param mixed $experience
     *
     * @return Booking
     */
    public function setExperience($experience)
    {
        $this->experience = $experience;

        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getChannelManager()
    {
        return $this->channelManager;
    }

    /**
     *
     * @param mixed $channelManager
     *
     * @return Booking
     */
    public function setChannelManager($channelManager)
    {
        $this->channelManager = $channelManager;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     *
     * @param array $components
     *
     * @return Booking
     */
    public function setComponents(array $components): Booking
    {
        $this->components = $components;

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

    /**
     *
     * @param BookingProductInterface $bookingProduct
     *
     * @return $this
     */
    public function addBookingProduct(BookingProductInterface $bookingProduct): BookingInterface
    {
        if (!$this->bookingProducts->contains($bookingProduct)) {
            $this->bookingProducts->add($bookingProduct);
        }

        return $this;
    }

    /**
     * Set bookingProducts
     *
     * @param Collection|BookingProduct[] $bookingProducts
     *
     * @return Booking
     */
    public function setBookingProducts(iterable $bookingProducts): BookingInterface
    {
        foreach ($bookingProducts as $product) {
            $product->setBooking($this);
        }

        $this->bookingProducts = $bookingProducts;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getReservationId(): string
    {
        return $this->getIdentifier();
    }

    /**
     *
     * @return BookingProduct[]|ArrayCollection|Collection
     */
    public function getBookingProducts()
    {
        return $this->bookingProducts;
    }

    /**
     *
     * @return GuestInterface[]|array
     */
    public function getGuests()
    {
        $guests = [];
        /* @var BookingProduct $bookingProduct */
        foreach ($this->bookingProducts as $bookingProduct) {
            $guests = array_merge($guests, $bookingProduct->getGuests()->toArray());
        }

        return $guests;
    }

    /**
     *
     * @return BookingProductRate[]
     */
    public function getRates()
    {
        $rates = [];
        /* @var BookingProduct $bookingProduct */
        foreach ($this->bookingProducts as $bookingProduct) {
            $rates = array_merge($rates, $bookingProduct->getRates()->toArray());
        }

        return $rates;
    }

    /**
     *
     * @param float $amount
     *
     * @return $this|BookingInterface
     */
    public function addTotalAmount(float $amount)
    {
        $this->totalAmount += $amount;

        return $this;
    }

    /**
     *
     * @return BookingProductInterface|null
     */
    public function firstBookingProduct(): ?BookingProductInterface
    {
        return $this->getBookingProducts()->first();
    }

    /**
     * @return string
     */
    public function getBookingProductsName(): string
    {
        $names = [];
        /** @var BookingProduct $bookingProduct */
        foreach ($this->bookingProducts as $bookingProduct) {
            $names[] = $bookingProduct->getProduct()->getName();
        }

        return implode(',', $names);
    }

    /**
     * @return string
     */
    public function getCreatedDateFormatted(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    /**
     * Get startDate.
     *
     * @return string
     */
    public function getStartDateFormatted(): string
    {
        return $this->startDate->format('Y-m-d H:i:s');
    }

    /**
     * Get startDate.
     *
     * @return string
     */
    public function getEndDateFormatted(): string
    {
        return $this->endDate->format('Y-m-d H:i:s');
    }

    /**
     * Gets the total guests.
     *
     * @return     integer  The total guests.
     */
    public function getTotalGuests(): int
    {
        $totalGuests = 0;
        foreach ($this->getBookingProducts() as $product) {
            $totalGuests += sizeof($product->getGuests());
        }

        return $totalGuests;
    }

    /**
     *
     * @return BookingProduct
     */
    public function getFirstBooking()
    {
        return $this->bookingProducts->first();
    }


    /**
     * @return GuestInterface|null
     */
    public function getMainGuest()
    {
        foreach ($this->getGuests() as $guest) {
            if ($guest->isMain()) {
                return $guest;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return BookingStatus::CONFIRMED === $this->status;
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return BookingStatus::CANCELLED === $this->status;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            LogKey::INTERNAL_ID        => (string) $this->getId(),
            LogKey::IDENTIFIER_KEY     => $this->getIdentifier(),
            LogKey::TYPE_KEY           => TransactionType::BOOKING,
            LogKey::AMOUNT             => (float) $this->getTotalAmount(),
            LogKey::CURRENCY           => $this->getCurrency(),
            LogKey::COMMENT            => $this->getComments(),
            LogKey::VOUCHER_NUMBER     => $this->getVoucherNumber(),
            LogKey::EXPERIENCE_KEY     => $this->getExperience() ? $this->getExperience()->toArray() : null,
            LogKey::PARTNER_ID_KEY     => $this->getPartner() ? $this->getPartner()->getIdentifier() : '',
            LogKey::CREATED_AT_KEY     => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d H:i:s') : '',
            LogKey::UPDATED_AT_KEY     => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d H:i:s') : '',
            LogKey::START_DATE         => $this->getStartDate() ? $this->getStartDate()->format('Y-m-d H:i:s') : '',
            LogKey::END_DATE           => $this->getEndDate() ? $this->getEndDate()->format('Y-m-d H:i:s') : '',
            LogKey::STATUS_KEY         => $this->getStatus(),
            LogKey::TRANSACTION_ID_KEY => $this->getTransaction() ? $this->getTransaction()->getTransactionId() : '',
            LogKey::CM_KEY             => $this->getPartner()->getChannelManager() ? $this->getPartner()->getChannelManager()->getIdentifier() : '',
        ];
    }
}
