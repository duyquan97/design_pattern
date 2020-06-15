<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\TransactionStatus;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TransactionController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class TransactionController
{
    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CmhubLogger $logger
     *
     * @return JsonResponse
     *
     * @throws NotFoundHttpException|BadRequestHttpException
     */
    public function updateStatusAction(Request $request, EntityManagerInterface $entityManager, CmhubLogger $logger)
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['transaction_id']) || !isset($data['status'])) {
            throw new BadRequestHttpException('either "transaction_id" or "status" is missing');
        }

        if (!in_array($data['status'], TransactionStatus::ALL)) {
            throw new BadRequestHttpException(sprintf('Invalid transaction status "%s"', $data['status']));
        }

        $transaction = $entityManager->getRepository(Transaction::class)
            ->findOneBy(['transactionId' => $data['transaction_id']]);

        if (!$transaction) {
            throw new BadRequestHttpException(sprintf('transaction_id "%s" not found', $data['transaction_id']));
        }

        $transaction->setStatus($data['status']);

        if (isset($data['statusCode'])) {
            $transaction->setStatusCode($data['statusCode']);
        }

        if (isset($data['response'])) {
            $transaction->setResponse($data['response']);
        }

        $entityManager->persist($transaction);
        $entityManager->flush();

        $logger->addOperationInfo(LogAction::EAI_UPDATE_CALLBACK, null, $this);

        return new JsonResponse(['status' => 'ok']);
    }
}
