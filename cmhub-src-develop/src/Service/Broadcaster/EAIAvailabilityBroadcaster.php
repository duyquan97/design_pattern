<?php

namespace App\Service\Broadcaster;

use App\Entity\Availability;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Exception\CmHubException;
use App\Exception\EAIClientException;
use App\Exception\EmptyRequestException;
use App\Exception\MissingTransactionDataException;
use App\Exception\NormalizerNotFoundException;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Repository\AvailabilityRepository;
use App\Service\EAI\EAIProcessor;

/**
 * Class EAIAvailabilityBroadcaster
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EAIAvailabilityBroadcaster implements BroadcasterInterface
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
     * @var EAIProcessor
     */
    private $eaiProcessor;

    /**
     * EAIAvailabilityBroadcaster constructor.
     *
     * @param AvailabilityRepository $availabilityRepository
     * @param ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory
     * @param EAIProcessor $eaiProcessor
     */
    public function __construct(AvailabilityRepository $availabilityRepository, ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory, EAIProcessor $eaiProcessor)
    {
        $this->availabilityRepository = $availabilityRepository;
        $this->productAvailabilityCollectionFactory = $productAvailabilityCollectionFactory;
        $this->eaiProcessor = $eaiProcessor;
    }

    /**
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function support(Transaction $transaction): bool
    {
        return TransactionChannel::EAI === $transaction->getChannel() && TransactionType::AVAILABILITY === $transaction->getType();
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

        if ($collection->isEmpty()) {
            throw new EmptyRequestException();
        }

        $response = $this
            ->eaiProcessor
            ->updateAvailabilities($collection);

        $this->availabilityRepository->clear();

        return $transaction
            ->setTransactionId($response->getTransactionId())
            ->setStatus($response->getStatus())
            ->setStatusCode($response->getStatusCode())
            ->setResponse($response->getResponse())
            ->setRequest($response->getRequest())
            ->setSentAt(date_create());
    }
}
