<?php

namespace App\Model;

use App\Entity\Partner;
use App\Entity\Product;

/**
 * Interface ProductInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface ProductInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;

    /**
     *
     * @return string
     */
    public function getIdentifier(): ?string;

    /**
     *
     * @return PartnerInterface
     */
    public function getPartner(): ?PartnerInterface;

    /**
     *
     * @return null|string
     */
    public function getName(): ?string;

    /**
     *
     * @return null|string
     */
    public function getDescription(): ?string;

    /**
     *
     * @return bool
     */
    public function hasLinkedProducts(): bool;

    /**
     *
     * @return ProductInterface[]
     */
    public function getLinkedProducts();

    /**
     *
     * @return bool
     */
    public function isMaster(): bool;

    /**
     * @return bool
     */
    public function isReservable(): bool;

    /**
     *
     * @return ProductInterface
     */
    public function getMasterProduct(): ?ProductInterface;

    /**
     *
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime;

    /**
     *
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime;

    /**
     *
     * @param Partner $partner
     *
     * @return ProductInterface
     */
    public function setPartner(?Partner $partner): ProductInterface;
}
