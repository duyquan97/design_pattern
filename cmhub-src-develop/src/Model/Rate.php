<?php

namespace App\Model;

use App\Entity\Transaction;

/**
 * Class Rate
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Rate implements RateInterface
{
    const SBX_RATE_PLAN_NAME = 'Smartbox Standard Rate';
    const SBC_PLAN_REGIME = 'Standard';

    /**
     * @var \DateTime
     */
    private $start;

    /**
     * @var \DateTime
     */
    private $end;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var string
     */
    protected $currency;

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
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     *
     * @return \DateTime
     */
    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    /**
     *
     * @param \DateTime $start
     *
     * @return Rate
     */
    public function setStart(\DateTime $start): RateInterface
    {
        $this->start = $start->setTime(0, 0, 0);

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     *
     * @param \DateTime $end
     *
     * @return Rate
     */
    public function setEnd(\DateTime $end): RateInterface
    {
        $this->end = $end->setTime(0, 0, 0);

        return $this;
    }

    /**
     *
     * @return float
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     *
     * @param float $amount
     *
     * @return Rate
     */
    public function setAmount(float $amount): RateInterface
    {
        $this->amount = $amount;

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
     * @return Rate
     */
    public function setProduct(ProductInterface $product): RateInterface
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get currency.
     *
     * @return string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * Set currency.
     *
     * @param string $currency
     *
     * @return Rate
     */
    public function setCurrency(?string $currency): RateInterface
    {
        $this->currency = $currency;

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
     *
     * @param PartnerInterface $partner
     *
     * @return self
     */
    public function setPartner(PartnerInterface $partner): RateInterface
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
    public function setTransaction(?Transaction $transaction): RateInterface
    {
        $this->transaction = $transaction;

        return $this;
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
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt): RateInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     *
     * @param \DateTime $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(\DateTime $updatedAt): RateInterface
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
