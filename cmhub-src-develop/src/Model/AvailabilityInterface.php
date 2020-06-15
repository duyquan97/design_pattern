<?php

namespace App\Model;

use App\Entity\Transaction;

/**
 * Interface AvailabilityInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface AvailabilityInterface
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
     * @return self
     */
    public function setStart(\DateTime $start): AvailabilityInterface;

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
    public function setEnd(\DateTime $end): AvailabilityInterface;

    /**
     *
     * @return int
     */
    public function getStock();

    /**
     *
     * @param int|null $stock
     *
     * @return self
     */
    public function setStock(?int $stock): AvailabilityInterface;

    /**
     *
     * @return ProductInterface
     */
    public function getProduct(): ?ProductInterface;

    /**
     *
     * @param ProductInterface $product
     *
     * @return $this
     */
    public function setProduct(ProductInterface $product);

    /**
     *
     * @return boolean|null
     */
    public function isStopSale();

    /**
     * @param bool $stopSale
     *
     * @return self
     */
    public function setStopSale(bool $stopSale): AvailabilityInterface;

    /**
     * @return Transaction|null
     */
    public function getTransaction(): ?Transaction;

    /**
     *
     * @param \DateTime $date
     *
     * @return self
     */
    public function setDate(\DateTime $date): AvailabilityInterface;

    /**
     *
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt): AvailabilityInterface;
}
