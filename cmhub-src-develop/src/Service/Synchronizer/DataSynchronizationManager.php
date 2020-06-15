<?php

namespace App\Service\Synchronizer;

use App\Exception\SynchronizerNotFoundException;
use App\Model\PartnerInterface;

/**
 * Class DataSynchronizationManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class DataSynchronizationManager
{
    /**
     * @var SynchronizerInterface[]
     */
    private $synchronizers;

    /**
     * DataSynchronizationManager constructor.
     *
     * @param SynchronizerInterface[] $synchronizers
     */
    public function __construct(array $synchronizers)
    {
        $this->synchronizers = $synchronizers;
    }

    /**
     * @param PartnerInterface $partner
     * @param \DateTime        $start
     * @param \DateTime        $end
     * @param string           $type
     *
     * @return array
     *
     * @throws SynchronizerNotFoundException
     */
    public function sync(PartnerInterface $partner, \DateTime $start, \DateTime $end, string $type): array
    {
        /** @var SynchronizerInterface $synchronizer */
        foreach ($this->synchronizers as $synchronizer) {
            if ($synchronizer->support($type)) {
                return $synchronizer->sync($partner, $start, $end);
            }
        }

        throw new SynchronizerNotFoundException();
    }
}
