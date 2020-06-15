<?php

namespace App\Message\Factory;

use App\Message\TransactionScheduled;

/**
 * Class TransactionScheduledFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class TransactionScheduledFactory
{
    /**
     * @param string $identifier
     *
     * @return TransactionScheduled
     */
    public function create(string $identifier): TransactionScheduled
    {
        return new TransactionScheduled($identifier);
    }
}
