<?php

namespace App\Service\Broadcaster;

use App\Entity\Availability;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Exception\CmHubException;
use App\Exception\IresaClientException;
use App\Exception\MissingTransactionDataException;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Repository\AvailabilityRepository;
use App\Service\Iresa\IresaApi;

/**
 * Class IresaAvailabilityBroadcaster
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaAvailabilityBroadcaster implements BroadcasterInterface
{
    /**
     * @var AvailabilityRepository
     */
    private $availabilityRepository;

    /**
     *
     * @var ProductAvailabilityCollectionFactory
     */
    private $productAvailabilityCollectionFactory;

    /**
     * @var IresaApi
     */
    private $iresaApi;

    /**
     * IresaAvailabilityBroadcaster constructor.
     *
     * @param AvailabilityRepository               $availabilityRepository
     * @param ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory
     * @param IresaApi                             $iresaApi
     */
    public function __construct(AvailabilityRepository $availabilityRepository, ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory, IresaApi $iresaApi)
    {
        $this->availabilityRepository = $availabilityRepository;
        $this->productAvailabilityCollectionFactory = $productAvailabilityCollectionFactory;
        $this->iresaApi = $iresaApi;
    }

    /**
     *
     * @param Transaction $transaction
     *
     * @return Transaction
     *
     * @throws CmHubException
     * @throws IresaClientException
     * @throws MissingTransactionDataException
     */
    public function broadcast(Transaction $transaction): Transaction
    {
        /* @var Availability[] $availabilities */
        $availabilities = $this
            ->availabilityRepository
            ->findBy(
                [
                    'transaction' => $transaction,
                ]
            );

        if (!$availabilities) {
            throw new MissingTransactionDataException($transaction);
        }

        $collection = $this
            ->productAvailabilityCollectionFactory
            ->create(
                $transaction->getPartner()
            );

        foreach ($availabilities as $availability) {
            $collection->addAvailability($availability);
        }

        $this
            ->iresaApi
            ->updateAvailabilities($collection);

        $this->availabilityRepository->clear();

        return $transaction;
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function support(Transaction $transaction): bool
    {
        return TransactionChannel::IRESA === $transaction->getChannel() && TransactionType::AVAILABILITY === $transaction->getType();
    }
}
