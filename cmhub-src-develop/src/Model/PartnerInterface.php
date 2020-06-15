<?php

namespace App\Model;

use App\Entity\ChannelManager;
use App\Entity\Partner;

/**
 * Interface PartnerInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface PartnerInterface
{
    /**
     *
     * @return null|string
     */
    public function getName(): ?string;

    /**
     *
     * @return null|string
     */
    public function getIdentifier(): ?string;

    /**
     *
     * @return null|string
     */
    public function getUsername(): ?string;

    /**
     * TODO: Create model interface and class instead of using Entity/ChannelManager
     *
     * @return ChannelManager
     */
    public function getChannelManager();

    /**
     * @return null|string
     */
    public function getCurrency(): ?string;

    /**
     *
     * @return ProductInterface[]
     */
    public function getProducts();

    /**
     * @param string $currency
     *
     * @return Partner
     */
    public function setCurrency(string $currency): Partner;

    /**
     * @return \DateTime|null
     */
    public function getConnectedAt(): ?\DateTime;

    /**
     * @param \DateTime|null $connectedAt
     *
     * @return Partner
     */
    public function setConnectedAt(?\DateTime $connectedAt): Partner;

    /**
     *
     * @return boolean
     */
    public function isEnabled(): bool;
}
