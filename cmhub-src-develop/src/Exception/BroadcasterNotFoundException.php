<?php

namespace App\Exception;

use App\Entity\Transaction;

/**
 * Class BroadcasterNotFoundException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BroadcasterNotFoundException extends CmHubException
{
    const TYPE = 'broadcaster_not_found';
    const MESSAGE = 'Not found any broadcaster for transaction with channel "%s" and type "%s"';

    /**
     * SerializerNotFoundException constructor.
     *
     * @param Transaction $transaction
     * @param int         $status
     */
    public function __construct(Transaction $transaction, int $status = 500)
    {
        parent::__construct(sprintf(static::MESSAGE, $transaction->getChannel(), $transaction->getType()), $status);
    }

    /**
     *
     * @return string
     */
    public function getExceptionType(): string
    {
        return static::TYPE;
    }
}
