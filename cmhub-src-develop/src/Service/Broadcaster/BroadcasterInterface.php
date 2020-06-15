<?php

namespace App\Service\Broadcaster;

use App\Entity\Transaction;
use App\Exception\CmHubException;
use App\Exception\EAIClientException;
use App\Exception\EmptyRequestException;
use App\Exception\IresaClientException;
use App\Exception\MissingTransactionDataException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BroadcasterInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface BroadcasterInterface
{
    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function support(Transaction $transaction): bool;

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     *
     * @throws EmptyRequestException
     * @throws CmHubException
     * @throws GuzzleException
     * @throws MissingTransactionDataException
     * @throws IresaClientException
     * @throws EAIClientException
     */
    public function broadcast(Transaction $transaction): Transaction;
}
