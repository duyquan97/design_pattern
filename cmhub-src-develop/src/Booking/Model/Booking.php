<?php

namespace App\Booking\Model;

use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Booking
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Booking
{
    public const CONFIRM = 'confirm';
    public const CANCEL = 'cancel';

    /**
     * @var string
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $identifier;

    /**
     * @var string
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Choice(choices = { "confirm", "cancel" }, message="The value you provided is not valid. 'confirm' or 'cancel' allowed")
     */
    private $status;

    /**
     * @var DateTimeInterface
     *
     * @Assert\NotNull()
     */
    private $startDate;

    /**
     * @var DateTimeInterface
     *
     * @Assert\NotNull()
     */
    private $endDate;

    /**
     * @var string
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $partner;

    /**
     * @var DateTimeInterface
     *
     * @Assert\NotNull()
     */
    private $createdAt;

    /**
     * @var DateTimeInterface
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $currency;

    /**
     * @var string|null
     */
    private $voucherNumber;

    /**
     * @var float
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $price;

    /**
     * @var Experience
     *
     * @Assert\Valid()
     */
    private $experience;

    /**
     * @var Room[]
     *
     * @Assert\Valid()
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must specify at least one room_type"
     * )
     */
    private $roomTypes;

    /**
     * @var string
     *
     */
    private $requests;

    /**
     * @var string
     *
     */
    private $comments;

    /**
     * Booking constructor.
     */
    public function __construct()
    {
        $this->experience = new Experience();
    }

    /**
     * @return null|Room[]
     */
    public function getRoomTypes(): ?array
    {
        return $this->roomTypes;
    }

    /**
     * @param Room[] $roomTypes
     *
     * @return Booking
     */
    public function setRoomTypes(array $roomTypes): Booking
    {
        $this->roomTypes = $roomTypes;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
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
     * @return null|string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
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
     * @return null|DateTimeInterface
     */
    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * @param DateTimeInterface $startDate
     *
     * @return Booking
     */
    public function setStartDate(?DateTimeInterface $startDate): Booking
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return null|DateTimeInterface
     */
    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @param DateTimeInterface $endDate
     *
     * @return Booking
     */
    public function setEndDate(?DateTimeInterface $endDate): Booking
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPartner(): ?string
    {
        return $this->partner;
    }

    /**
     * @param string $partner
     *
     * @return Booking
     */
    public function setPartner(string $partner): Booking
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * @return null|DateTimeInterface
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface $createdAt
     *
     * @return Booking
     */
    public function setCreatedAt(?DateTimeInterface $createdAt): Booking
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return null|DateTimeInterface
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeInterface $updatedAt
     *
     * @return Booking
     */
    public function setUpdatedAt(?DateTimeInterface $updatedAt): Booking
    {
        $this->updatedAt = $updatedAt;

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
     * @return Booking
     */
    public function setCurrency(string $currency): Booking
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getVoucherNumber(): ?string
    {
        return $this->voucherNumber;
    }

    /**
     * @param string $voucherNumber
     *
     * @return Booking
     */
    public function setVoucherNumber(string $voucherNumber): Booking
    {
        $this->voucherNumber = $voucherNumber;

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
     * @return Booking
     */
    public function setPrice(float $price): Booking
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return null|Experience
     */
    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    /**
     * @param Experience $experience
     *
     * @return Booking
     */
    public function setExperience(Experience $experience): Booking
    {
        $this->experience = $experience;

        return $this;
    }

    /**
     *
     * @return string|null
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
    public function setRequests(?string $requests): Booking
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
    public function setComments(?string $comments): Booking
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return self::CONFIRM === $this->status;
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return self::CANCEL === $this->status;
    }
}
