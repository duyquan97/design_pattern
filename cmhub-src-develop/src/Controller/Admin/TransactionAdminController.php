<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Availability;
use App\Entity\Booking;
use App\Entity\ProductRate;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TransactionAdminController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
final class TransactionAdminController extends CRUDController
{
    private const AVAILABILITY_LIST_ROUTE = 'admin_app_availability_list';
    private const BOOKING_LIST_ROUTE = 'admin_app_booking_list';
    private const PRODUCT_RATE_LIST_ROUTE = 'admin_app_productrate_list';
    private const BROADCAST_TRANSACTION_LIST_ROUTE = 'admin_app_transaction_list';

    /**
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     *
     * @return RedirectResponse
     */
    public function entityAction(Request $request, EntityManagerInterface $entityManager)
    {
        $transactionId = $request->get('id');
        $transaction = $entityManager->getRepository(Transaction::class)->find($transactionId);
        if (!$transaction) {
            return new RedirectResponse($this->generateUrl(self::BROADCAST_TRANSACTION_LIST_ROUTE));
        }

        switch ($transaction->getType()) {
            case TransactionType::AVAILABILITY:
                $availability = $entityManager
                    ->getRepository(Availability::class)
                    ->findOneBy(['transaction' => $transactionId]);

                if (!$availability) {
                    return new RedirectResponse($this->generateUrl(self::BROADCAST_TRANSACTION_LIST_ROUTE));
                }

                return new RedirectResponse(
                    $this->generateUrl(self::AVAILABILITY_LIST_ROUTE, ['filter[transaction__transactionId][value]' => $transaction->getTransactionId()])
                );
            case TransactionType::BOOKING:
                $booking = $entityManager
                    ->getRepository(Booking::class)
                    ->findOneBy(['transaction' => $transactionId]);

                if (!$booking) {
                    return new RedirectResponse($this->generateUrl(self::BROADCAST_TRANSACTION_LIST_ROUTE));
                }

                return new RedirectResponse(
                    $this->generateUrl(self::BOOKING_LIST_ROUTE, ['filter[transaction__transactionId][value]' => $transaction->getTransactionId()])
                );
            default:
                $productRate = $entityManager
                    ->getRepository(ProductRate::class)
                    ->findOneBy(['transaction' => $transactionId]);

                if (!$productRate) {
                    return new RedirectResponse($this->generateUrl(self::BROADCAST_TRANSACTION_LIST_ROUTE));
                }

                return new RedirectResponse(
                    $this->generateUrl(self::PRODUCT_RATE_LIST_ROUTE, ['filter[transaction__transactionId][value]' => $transaction->getTransactionId()])
                );
        }
    }
}
