<?php

namespace App\Service\ChannelManager\BB8\Operation\Model;

use App\Entity\Partner;

/**
 * Class GetRooms
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GetRooms
{
    /**
     * @var Partner[]
     */
    private $partners;

    /**
     * @var \DateTime
     */
    private $externalUpdatedFrom;

    /**
     * @return Partner[]
     */
    public function getPartners(): ?array
    {
        return $this->partners;
    }

    /**
     * @param Partner[] $partners
     *
     * @return GetRooms
     */
    public function setPartners(array $partners)
    {
        $this->partners = $partners;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExternalUpdatedFrom(): ?\DateTime
    {
        return $this->externalUpdatedFrom;
    }

    /**
     * @param \DateTime $externalUpdatedFrom
     *
     * @return GetRooms
     */
    public function setExternalUpdatedFrom(\DateTime $externalUpdatedFrom = null)
    {
        $this->externalUpdatedFrom = $externalUpdatedFrom;

        return $this;
    }
}
