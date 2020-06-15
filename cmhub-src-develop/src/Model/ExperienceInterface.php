<?php

namespace App\Model;

use App\Entity\Partner;

/**
 * Class ExperienceInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface ExperienceInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;


    /**
     * @return string|null
     */
    public function getIdentifier(): ?string;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @return float|null
     */
    public function getPrice(): ?float;

    /**
     * @return float|null
     */
    public function getCommission(): ?float;

    /**
     * @return string|null
     */
    public function getCommissionType(): ?string;

    /**
     * @return Partner|null
     */
    public function getPartner(): ?Partner;

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime;

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime;
}
