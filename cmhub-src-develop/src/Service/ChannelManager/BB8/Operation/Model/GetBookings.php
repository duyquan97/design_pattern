<?php

namespace App\Service\ChannelManager\BB8\Operation\Model;

use App\Entity\Partner;

/**
 * Class GetBookings
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GetBookings
{

    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var \DateTime
     */
    private $endDate;

    /**
     * @var Partner[]
     */
    private $partners;

    /**
     *
     * @return \DateTime
     */
    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    /**
     *
     * @param \DateTime $startDate
     *
     * @return GetBookings
     */
    public function setStartDate(\DateTime $startDate): GetBookings
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     *
     * @param \DateTime $endDate
     *
     * @return GetBookings
     */
    public function setEndDate(\DateTime $endDate): GetBookings
    {
        $this->endDate = $endDate;

        return $this;
    }

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
     * @return GetBookings
     */
    public function setPartners(array $partners)
    {
        $this->partners = $partners;

        return $this;
    }
}
