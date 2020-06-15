<?php

namespace App\Model;

use App\Entity\Transaction;

/**
 * Interface ProductRateCollectionInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface ProductRateCollectionInterface
{
    /**
     *
     * @param ProductRateInterface $productRate
     *
     * @return $this
     */
    public function addProductRate(ProductRateInterface $productRate);

    /**
     *
     * @return ProductRate[]
     */
    public function getProductRates(): array;

    /**
     *
     * @return PartnerInterface
     */
    public function getPartner(): PartnerInterface;

    /**
     *
     * @param ProductInterface $product
     *
     * @return ProductRateInterface
     */
    public function getProductRate(ProductInterface $product): ProductRateInterface;

    /**
     *
     * @param ProductRateInterface[] $productRates
     *
     * @return $this
     */
    public function setProductRates(array $productRates);

    /**
     *
     * @param Transaction|null $transaction
     *
     * @return $this
     */
    public function setTransaction(?Transaction $transaction);

    /**
     *
     * @return Transaction|null
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
     * @return ProductRateCollectionInterface
     */
    public function setChannel(string $channel);

    /**
     *
     * @return string
     */
    public function getChannel(): string;
}
