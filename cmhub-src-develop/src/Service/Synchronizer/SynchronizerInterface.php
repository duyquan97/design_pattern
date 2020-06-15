<?php

namespace App\Service\Synchronizer;

use App\Model\PartnerInterface;

/**
 * Class SynchronizerInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface SynchronizerInterface
{
    public const ONE_WEEK_INTERVAL = '+7 day';

    /**
     * @param PartnerInterface $partner
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return array
     */
    public function sync(PartnerInterface $partner, \DateTime $start, \DateTime $end);

    /**
     * @param string $type
     *
     * @return bool
     */
    public function support(string $type): bool;
}
