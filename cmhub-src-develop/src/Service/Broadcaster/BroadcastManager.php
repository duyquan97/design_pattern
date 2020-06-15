<?php

namespace App\Service\Broadcaster;

use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionStatus;
use App\Exception\BroadcasterNotFoundException;
use App\Exception\CmHubException;
use App\Exception\EAIClientException;
use App\Exception\EmptyRequestException;
use App\Exception\IresaClientException;
use App\Exception\MissingTransactionDataException;
use App\Utils\Monolog\CmhubLogger;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BroadcastManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BroadcastManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CmhubLogger
     */
    private $logger;

    /**
     * @var BroadcasterInterface[]
     */
    private $broadcasters;

    /**
     * @var int
     */
    private $maximumRetries;

    /**
     * BroadcastManager constructor.
     *
     * @param array                  $broadcasters
     * @param CmhubLogger            $logger
     * @param EntityManagerInterface $entityManager
     * @param int                    $maximumRetries
     */
    public function __construct(array $broadcasters, CmhubLogger $logger, EntityManagerInterface $entityManager, int $maximumRetries)
    {
        $this->broadcasters = $broadcasters;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->maximumRetries = $maximumRetries;
    }

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     */
    public function broadcast(Transaction $transaction): Transaction
    {
        try {
            if (TransactionStatus::FAILED === $transaction->getStatus()) {
                $transaction->increaseRetries();
            }

            $transaction = $this
                ->getBroadcaster($transaction)
                ->broadcast($transaction)
                ->setSentAt(new \DateTime());

            if (TransactionChannel::EAI !== $transaction->getChannel()) {
                $transaction->setStatus(TransactionStatus::SUCCESS);
            }
        } catch (IresaClientException $iresaClientException) {
            $transaction
                ->setStatus(TransactionStatus::FAILED)
                ->setResponse($iresaClientException->getResponse());
        } catch (EmptyRequestException $exception) {
            $transaction
                ->setStatus(TransactionStatus::ERROR)
                ->setResponse($exception->getMessage());
        } catch (EAIClientException $exception) {
            $transaction
                ->setStatus(TransactionStatus::FAILED)
                ->setResponse($exception->getResponse())
                ->setRequest($exception->getRequest())
                ->setStatusCode($exception->getStatusCode());
        } catch (CmHubException | BroadcasterNotFoundException $exception) {
            $transaction
                ->setStatus(TransactionStatus::FAILED)
                ->setResponse($exception->getMessage());
        } catch (MissingTransactionDataException $exception) {
            $transaction
                ->setStatus(TransactionStatus::DEPRECATED)
                ->setResponse($exception->getMessage());
        } catch (GuzzleException $exception) {
            $transaction
                ->setStatus(TransactionStatus::FAILED)
                ->setResponse($exception->getMessage());
        } catch (\Throwable $exception) {
            $transaction
                ->setStatus(TransactionStatus::FAILED)
                ->setResponse($exception->getMessage());
        }

        if (TransactionStatus::FAILED === $transaction->getStatus() && $transaction->getRetries() >= $this->maximumRetries) {
            $transaction->setStatus(TransactionStatus::ERROR);
        }

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        $this
            ->logger
            ->addTransactionInfo($transaction, $this);

        return $transaction;
    }

    /**
     * @param Transaction $transaction
     *
     * @return BroadcasterInterface
     *
     * @throws BroadcasterNotFoundException
     */
    public function getBroadcaster(Transaction $transaction)
    {
        foreach ($this->broadcasters as $broadcaster) {
            if ($broadcaster->support($transaction)) {
                return $broadcaster;
            }
        }

        throw new BroadcasterNotFoundException($transaction);
    }
}
