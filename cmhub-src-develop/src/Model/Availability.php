<?php

namespace App\Model;

use App\Entity\Transaction;

/**
 * Class Availability
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Availability implements AvailabilityInterface
{
    /**
     *
     * @var \DateTime
     */
    private $start;

    /**
     *
     * @var \DateTime
     */
    private $end;

    /**
     *
     * @var int
     */
    protected $stock;

    /**
     * @var bool
     */
    protected $stopSale;

    /**
     *
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var PartnerInterface
     */
    protected $partner;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var \DateTime
     *
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     */
    protected $updatedAt;

    /**
     * Availability constructor.
     *
     * @param ProductInterface $product
     */
    public function __construct(ProductInterface $product = null)
    {
        $this->product = $product;
    }

    /**
     *
     * @return \DateTime
     */
    public function getStart(): \DateTime
    {
        return $this->start;
    }

    /**
     *
     * @param \DateTime $start
     *
     * @return Availability
     */
    public function setStart(\DateTime $start): AvailabilityInterface
    {
        $this->start = $start->setTime(0, 0, 0);

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    /**
     *
     * @param \DateTime $end
     *
     * @return Availability
     */
    public function setEnd(\DateTime $end): AvailabilityInterface
    {
        $this->end = $end->setTime(0, 0, 0);

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return Availability
     */
    public function setDate(\DateTime $date): AvailabilityInterface
    {
        $this->setStart($date);
        $this->setEnd($date);

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->getStart();
    }

    /**
     *
     * @return int|null
     */
    public function getStock(): ?int
    {
        return $this->stock;
    }

    /**
     *
     * @param int|null $stock
     *
     * @return Availability
     */
    public function setStock(?int $stock): AvailabilityInterface
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isStopSale(): ?bool
    {
        return $this->stopSale;
    }

    /**
     * @param bool $stopSale
     *
     * @return $this
     */
    public function setStopSale(bool $stopSale): AvailabilityInterface
    {
        $this->stopSale = $stopSale;

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
     * @return $this
     */
    public function setProduct(ProductInterface $product): AvailabilityInterface
    {
        $this->product = $product;

        return $this;
    }

    /**
     *
     * @return PartnerInterface
     */
    public function getPartner(): ?PartnerInterface
    {
        return $this->partner;
    }

    /**
     * @param PartnerInterface $partner
     *
     * @return Availability
     */
    public function setPartner(PartnerInterface $partner): AvailabilityInterface
    {
        $this->partner = $partner;

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
     * @param Transaction $transaction
     *
     * @return self
     */
    public function setTransaction(?Transaction $transaction): AvailabilityInterface
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return string
     */
    public function getStopSaleString(): string
    {
        return $this->stopSale ? 'Yes' : 'No';
    }

    /**
     *
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     *
     * @param \DateTime $createdAt
     *
     * @return Availability
     */
    public function setCreatedAt(\DateTime $createdAt): AvailabilityInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     *
     * @param \DateTime $updatedAt
     *
     * @return Availability
     */
    public function setUpdatedAt(\DateTime $updatedAt): AvailabilityInterface
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
