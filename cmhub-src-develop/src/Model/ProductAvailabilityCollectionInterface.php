<?php

namespace App\Model;

use App\Entity\Transaction;

/**
 * Interface ProductAvailabilityCollectionInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface ProductAvailabilityCollectionInterface
{
    /**
     *
     * @return PartnerInterface
     */
    public function getPartner(): PartnerInterface;

    /**
     *
     * @return ProductAvailabilityInterface[]
     */
    public function getProductAvailabilities(): array;

    /**
     *
     * @param ProductAvailabilityInterface $productAvailability
     *
     * @return ProductAvailabilityCollectionInterface
     */
    public function addProductAvailability(ProductAvailabilityInterface $productAvailability): ProductAvailabilityCollectionInterface;

    /**
     *
     * @param ProductInterface $product
     * @param \DateTime        $date
     *
     * @return AvailabilityInterface|null
     */
    public function getByProductAndDate(ProductInterface $product, \DateTime $date): ?AvailabilityInterface;

    /**
     *
     * @param Transaction $transaction
     *
     * @return $this
     */
    public function setTransaction(Transaction $transaction): self;

    /**
     *
     * @return Transaction
     */
    public function getTransaction(): ?Transaction;

    /**
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     *
     * @param string $channel
     *
     * @return ProductAvailabilityCollectionInterface
     */
    public function setChannel(string $channel);

    /**
     *
     * @return string
     */
    public function getChannel(): string;

    /**
     * @return string
     */
    public function getSource(): string;

    /**
     * @param string $source
     *
     * @return ProductAvailabilityCollectionInterface
     */
    public function setSource(string $source);

    /**
     *
     * @return array
     */
    public function getAvailabilities(): array;
}
