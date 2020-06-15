<?php

namespace App\Entity;

use App\Utils\Monolog\LoggableInterface;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EaiTransaction
 *
 * @ORM\Table(name="broadcasts",indexes={@ORM\Index(name="broadcast", columns={"transaction_id", "status", "type"})})
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Transaction implements LoggableInterface
{
    /**
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $transactionId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $statusCode = '';

    /**
     *
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    /**
     *
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $updatedAt;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\Choice(choices = { "scheduled", "failed", "sent" , "success"}, message="The value you provided is not valid. 'scheduled' or 'failed' or 'sent' or 'success' allowed"))
     */
    private $status = TransactionStatus::SCHEDULED;

    /**
     * Channel to broadcast the data (iresa, eai, none, cm)
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $channel;

    /**
     * Refers to what type of data is being broadcasted. TransactionType constants.
     *
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $type;

    /**
     *
     * @var Partner
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Partner")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $partner;

    /**
     * @var string
     */
    private $request = '';

    /**
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $response;

    /**
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false, options={"default": 0})
     */
    private $retries = 0;

    /**
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     *
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     *
     * @param string $transactionId
     *
     * @return Transaction
     */
    public function setTransactionId(?string $transactionId): Transaction
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getStatusCode(): ?string
    {
        return $this->statusCode;
    }

    /**
     *
     * @param string $statusCode
     *
     * @return Transaction
     */
    public function setStatusCode(string $statusCode): Transaction
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     *
     * @param \DateTime $createdAt
     *
     * @return Transaction
     */
    public function setCreatedAt(\DateTime $createdAt): Transaction
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     *
     * @param string $status
     *
     * @return Transaction
     */
    public function setStatus(string $status): Transaction
    {
        $this->status = $status;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     *
     * @param string $channel
     *
     * @return Transaction
     */
    public function setChannel(string $channel): Transaction
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     *
     * @param string $type
     *
     * @return Transaction
     */
    public function setType(string $type): Transaction
    {
        $this->type = $type;

        return $this;
    }

    /**
     *
     * @return Partner
     */
    public function getPartner(): ?Partner
    {
        return $this->partner;
    }

    /**
     *
     * @param Partner $partner
     *
     * @return Transaction
     */
    public function setPartner(Partner $partner): Transaction
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getRequest(): string
    {
        return $this->request;
    }

    /**
     *
     * @param string $request
     *
     * @return Transaction
     */
    public function setRequest(string $request): Transaction
    {
        $this->request = $request;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     *
     * @param string|null $response
     *
     * @return Transaction
     */
    public function setResponse(?string $response): Transaction
    {
        $this->response = $response;

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isSuccess()
    {
        return TransactionStatus::SUCCESS === $this->getStatus();
    }

    /**
     *
     * @return bool
     */
    public function isFailed()
    {
        return TransactionStatus::FAILED === $this->getStatus();
    }

    /**
     *
     * @return bool
     */
    public function isErrored()
    {
        return TransactionStatus::ERROR === $this->getStatus();
    }

    /**
     *
     * @return bool
     */
    public function isScheduled()
    {
        return TransactionStatus::SCHEDULED === $this->status;
    }

    /**
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            LogKey::TYPE_KEY             => LogType::TRANSACTION,
            LogKey::TRANSACTION_ID_KEY   => (string) $this->getTransactionId(),
            LogKey::INTERNAL_ID          => (string) $this->getId(),
            LogKey::STATUS_CODE_KEY      => (string) $this->getStatusCode(),
            LogKey::STATUS_KEY           => $this->getStatus(),
            LogKey::CHANNEL_KEY          => $this->getChannel(),
            LogKey::TRANSACTION_TYPE_KEY => $this->getType(),
            LogKey::PARTNER_ID_KEY       => $this->getPartner()->getIdentifier(),
            LogKey::PARTNER_NAME_KEY     => $this->getPartner()->getName(),
            LogKey::REQUEST_KEY          => $this->getRequest(),
            LogKey::RESPONSE_KEY         => $this->getResponse(),
            LogKey::RETRIES_KEY          => $this->getRetries(),
            LogKey::CM_KEY               => $this->getPartner()->getChannelManager() ? $this->getPartner()->getChannelManager()->getIdentifier() : '',
            LogKey::SENT_AT_KEY          => $this->getSentAt() ? $this->getSentAt()->format('Y-m-d H:i:s') : '',
            LogKey::CREATED_AT_KEY       => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d H:i:s') : '',
            LogKey::UPDATED_AT_KEY       => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d H:i:s') : '',
        ];
    }

    /**
     *
     * @return string
     */
    public function __toString(): string
    {
        return ($this->transactionId) ?? 'No transaction id';
    }

    /**
     *
     * @return \DateTime
     */
    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    /**
     * @param \DateTime $sentAt
     *
     * @return Transaction
     */
    public function setSentAt(\DateTime $sentAt): Transaction
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return Transaction
     */
    public function setUpdatedAt(\DateTime $updatedAt): Transaction
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getRetries(): ?int
    {
        return $this->retries;
    }

    /**
     * @param int $retries
     *
     * @return self
     */
    public function setRetries(int $retries): Transaction
    {
        $this->retries = $retries;

        return $this;
    }

    /**
     * @return Transaction
     */
    public function increaseRetries(): Transaction
    {
        $this->retries++;

        return $this;
    }
}
