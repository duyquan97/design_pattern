<?php

namespace App\Service\ChannelManager\SoapOta;

use App\Exception\CmHubException;

/**
 * Interface SoapOtaOperationInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface SoapOtaOperationInterface
{
    /**
     *
     * @param \StdClass $request
     *
     * @return array
     *
     * @throws CmHubException
     */
    public function handle(\StdClass $request): array;

    /**
     *
     * @param string $operation
     *
     * @return bool
     */
    public function supports(string $operation): bool;
}
