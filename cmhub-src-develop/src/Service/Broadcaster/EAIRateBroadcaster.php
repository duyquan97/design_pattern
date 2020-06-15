<?php

namespace App\Service\Broadcaster;

use App\Entity\ProductRate;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Exception\CmHubException;
use App\Exception\EAIClientException;
use App\Exception\EmptyRequestException;
use App\Exception\MissingTransactionDataException;
use App\Exception\NormalizerNotFoundException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Repository\ProductRateRepository;
use App\Service\EAI\EAIProcessor;

/**
 * Class EAIRateBroadcaster
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EAIRateBroadcaster implements BroadcasterInterface
{
    /**
     *
     * @var ProductRateRepository
     */
    private $productRateRepository;

    /**
     *
     * @var ProductRateCollectionFactory
     */
    private $productRateCollectionFactory;

    /**
     * @var EAIProcessor
     */
    private $eaiProcessor;

    /**
     * EAIRateBroadcaster constructor.
     *
     * @param ProductRateRepository        $productRateRepository
     * @param ProductRateCollectionFactory $productRateCollectionFactory
     * @param EAIProcessor                 $eaiProcessor
     */
    public function __construct(ProductRateRepository $productRateRepository, ProductRateCollectionFactory $productRateCollectionFactory, EAIProcessor $eaiProcessor)
    {
        $this->productRateRepository = $productRateRepository;
        $this->productRateCollectionFactory = $productRateCollectionFactory;
        $this->eaiProcessor = $eaiProcessor;
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function support(Transaction $transaction): bool
    {
        return TransactionChannel::EAI === $transaction->getChannel() && TransactionType::PRICE === $transaction->getType();
    }

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     *
     * @throws EmptyRequestException
     * @throws CmHubException
     * @throws NormalizerNotFoundException
     * @throws MissingTransactionDataException
     * @throws EAIClientException
     */
    public function broadcast(Transaction $transaction): Transaction
    {
        /* @var ProductRate $rate */
        $rates = $this
            ->productRateRepository
            ->findBy(
                [
                    'transaction' => $transaction,
                ]
            );

        if (!$rates) {
            throw new MissingTransactionDataException($transaction);
        }

        $collection = $this->productRateCollectionFactory->create($transaction->getPartner());
        foreach ($rates as $rate) {
            $collection->addRate($rate->getProduct(), $rate);
        }

        if ($collection->isEmpty()) {
            throw new EmptyRequestException();
        }

        $response = $this->eaiProcessor->updateRates($collection);

        $this->productRateRepository->clear();

        return $transaction
            ->setTransactionId($response->getTransactionId())
            ->setStatus($response->getStatus())
            ->setStatusCode($response->getStatusCode())
            ->setResponse($response->getResponse())
            ->setRequest($response->getRequest())
            ->setSentAt(date_create());
    }
}
