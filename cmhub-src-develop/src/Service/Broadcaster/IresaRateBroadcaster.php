<?php

namespace App\Service\Broadcaster;

use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Entity\ProductRate;
use App\Repository\ProductRateRepository;
use App\Exception\CmHubException;
use App\Exception\IresaClientException;
use App\Exception\MissingTransactionDataException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Service\Chaining\ChainingHelper;
use App\Service\Iresa\IresaApi;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class IresaRateBroadcaster
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaRateBroadcaster implements BroadcasterInterface
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
     *
     * @var IresaApi
     */
    private $iresaApi;

    /**
     *
     * @var ChainingHelper
     */
    private $chainingHelper;

    /**
     * IresaRateBroadcaster constructor.
     *
     * @param ProductRateRepository        $productRateRepository
     * @param ProductRateCollectionFactory $productRateCollectionFactory
     * @param IresaApi                     $iresaApi
     * @param ChainingHelper               $chainingHelper
     */
    public function __construct(ProductRateRepository $productRateRepository, ProductRateCollectionFactory $productRateCollectionFactory, IresaApi $iresaApi, ChainingHelper $chainingHelper)
    {
        $this->productRateRepository = $productRateRepository;
        $this->productRateCollectionFactory = $productRateCollectionFactory;
        $this->iresaApi = $iresaApi;
        $this->chainingHelper = $chainingHelper;
    }

    /**
     *
     * @param Transaction $transaction
     *
     * @return Transaction
     *
     * @throws CmHubException
     * @throws GuzzleException
     * @throws IresaClientException
     * @throws MissingTransactionDataException
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

        $collection = $this
            ->chainingHelper
            ->chainRates($collection);

        $this->iresaApi->updateRates($collection);
        $this->productRateRepository->clear();

        return $transaction;
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function support(Transaction $transaction): bool
    {
        return TransactionChannel::IRESA === $transaction->getChannel() && TransactionType::PRICE === $transaction->getType();
    }
}
