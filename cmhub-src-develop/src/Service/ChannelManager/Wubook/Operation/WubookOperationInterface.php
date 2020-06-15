<?php

namespace App\Service\ChannelManager\Wubook\Operation;

use App\Entity\Partner;

/**
 * Interface WubookOperationInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface WubookOperationInterface
{
    /**
     *
     * @param \stdClass $request
     * @param Partner   $partner
     *
     * @return array
     */
    public function handle(\stdClass $request, Partner $partner): array;

    /**
     *
     * @param string $operation
     *
     * @return bool
     */
    public function supports(string $operation): bool;
}
