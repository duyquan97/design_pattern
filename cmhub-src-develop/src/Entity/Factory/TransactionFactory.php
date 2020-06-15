<?php

namespace App\Entity\Factory;

use App\Entity\Transaction;
use App\Entity\TransactionStatus;
use App\Model\PartnerInterface;

/**
 * Class EAITransactionFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class TransactionFactory
{
    /**
     *
     * @param string                $type
     * @param string                $channel
     * @param string                $status
     * @param PartnerInterface|null $partner
     * @param string|null           $transactionId
     *
     * @return Transaction
     */
    public function create(string $type, string $channel, string $status = TransactionStatus::SCHEDULED, PartnerInterface $partner = null, string $transactionId = null)
    {
        $transaction = (new Transaction())
            ->setType($type)
            ->setChannel($channel)
            ->setStatus($status)
            ->setTransactionId(null !== $transactionId ? $transactionId : bin2hex(random_bytes(10)));

        if ($partner) {
            $transaction->setPartner($partner);
        }

        return $transaction;
    }
}
