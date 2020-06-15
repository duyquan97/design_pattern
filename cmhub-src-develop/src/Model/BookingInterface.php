<?php

namespace App\Model;

use App\Entity\ChannelManager;
use Doctrine\Common\Collections\Collection;

/**
 * Interface BookingInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface BookingInterface
{
    /**
     *
     * @return string
     */
    public function getReservationId(): string;

    /**
     *
     * @return \DateTime
     */
    public function getStartDate();

    /**
     *
     * @return \DateTime
     */
    public function getEndDate();

    /**
     *
     * @return float
     */
    public function getTotalAmount();

    /**
     *
     * @return string
     */
    public function getCurrency();

    /**
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * See BookingStatus constants
     *
     * @return string
     */
    public function getStatus();

    /**
     *
     * @return BookingProduct[]|Collection
     */
    public function getBookingProducts();

    /**
     *
     * @return null|string
     */
    public function getRequests();

    /**
     *
     * @return null|string
     */
    public function getComments();

    /**
     *
     * @return GuestInterface[]
     */
    public function getGuests();

    /**
     *
     * @return string
     */
    public function getVoucherNumber();

    /**
     *
     * @return RateInterface[]
     */
    public function getRates();

    /**
     *
     * @return ExperienceInterface
     */
    public function getExperience();

    /**
     *
     * @return PartnerInterface
     */
    public function getPartner();

    /**
     *
     * @return array
     */
    public function getComponents();

    /**
     *
     * @param array $components
     *
     * @return BookingInterface
     */
    public function setComponents(array $components);

    /**
     *
     * @param float $totalAmount
     *
     * @return $this
     */
    public function setTotalAmount(float $totalAmount);

    /**
     *
     * @param float $amount
     *
     * @return $this
     */
    public function addTotalAmount(float $amount);

    /**
     *
     * @param ChannelManager $channelManager
     *
     * @return $this
     */
    public function setChannelManager(ChannelManager $channelManager);

    /**
     * @return BookingProductInterface|null
     */
    public function firstBookingProduct(): ?BookingProductInterface;

    /**
     * @return bool
     */
    public function isConfirmed(): bool;

    /**
     *
     * @param bool $processed
     *
     * @return self
     */
    public function setProcessed(bool $processed): BookingInterface;

    /**
     *
     * @param string $comments
     *
     * @return self
     */
    public function setComments(?string $comments): BookingInterface;
}
