<?php

namespace App\Service\ChannelManager\BB8\Operation;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface BB8OperationInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface BB8OperationInterface
{
    /**
     *
     * @param Request $request
     *
     * @return array
     */
    public function handle(Request $request): array;

    /**
     *
     * @param string $operation
     *
     * @return bool
     */
    public function supports(string $operation): bool;
}
