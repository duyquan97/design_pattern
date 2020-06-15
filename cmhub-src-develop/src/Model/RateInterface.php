<?php

namespace App\Model;

/**
 * Interface RateInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface RateInterface
{
    /**
     *
     * @return \DateTime
     */
    public function getStart();

    /**
     *
     * @param \DateTime $start
     *
     * @return Rate
     */
    public function setStart(\DateTime $start): RateInterface;

    /**
     *
     * @return \DateTime
     */
    public function getEnd();

    /**
     *
     * @param \DateTime $end
     *
     * @return self
     */
    public function setEnd(\DateTime $end): RateInterface;

    /**
     *
     * @return float|null
     */
    public function getAmount(): ?float;

    /**
     *
     * @return ProductInterface
     */
    public function getProduct(): ?ProductInterface;

    /**
     *
     * @param float $amount
     *
     * @return self
     */
    public function setAmount(float $amount): RateInterface;

    /**
     * Set currency.
     *
     * @param string $currency
     *
     * @return self
     */
    public function setCurrency(?string $currency): RateInterface;
}
