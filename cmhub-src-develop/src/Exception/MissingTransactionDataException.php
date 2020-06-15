<?php

namespace App\Exception;

use App\Entity\Transaction;

/**
 * Class MissingTransactionDataException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class MissingTransactionDataException extends \Exception
{
    public const MESSAGE = 'Transaction type `%s` with id %s related data has not been found';

    /**
     *
     * @var Transaction
     */
    private $transaction;

    /**
     * MissingTransactionDataException constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;

        parent::__construct(sprintf(static::MESSAGE, $transaction->getType(), $transaction->getTransactionId()), 500);
    }

    /**
     *
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
