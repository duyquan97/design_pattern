<?php

namespace App\Entity;

use App\Model\RateInterface;
use App\Utils\Monolog\LoggableInterface;
use Doctrine\ORM\Mapping\UniqueConstraint;
use App\Utils\Monolog\LogKey;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Model\Rate as RateModel;

/**
 * Class ProductRate
 *
 * @ORM\Entity(repositoryClass="App\Repository\ProductRateRepository")
 * @ORM\Table(
 *    indexes={@ORM\Index(name="prate_idx", columns={"date"})},
 *    uniqueConstraints={@UniqueConstraint(name="rate_date_product_unique", columns={"date", "product_id"})}
 * )
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRate extends RateModel implements RateInterface, LoggableInterface
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
     *
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product")
     */
    protected $product;

    /**
     *
     * @var Partner
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Partner")
     */
    protected $partner;

    /**
     *
     * @var float
     *
     * @ORM\Column(type="float")
     */
    protected $amount;

    /**
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @var Transaction
     *
     * @ORM\ManyToOne(targetEntity="Transaction", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $transaction;

    /**
     *
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $createdAt;

    /**
     *
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $updatedAt;

    /**
     * ProductRate constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     *
     * @return \DateTime
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getDateFormatted(): string
    {
        return $this->date->format('Y-m-d H:i:s');
    }

    /**
     *
     * @param \DateTime $date
     *
     * @return ProductRate
     */
    public function setDate(\DateTime $date): ProductRate
    {
        $this->date = $date;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getStart(): ?\DateTime
    {
        return $this->date;
    }

    /**
     *
     * @return \DateTime
     */
    public function getEnd(): ?\DateTime
    {
        return $this->date;
    }

    /**
     *
     * @return string
     */
    public function getCreatedAtFormatted(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    /**
     *
     * @return string
     */
    public function getUpdatedAtFormatted(): string
    {
        return $this->updatedAt->format('Y-m-d H:i:s');
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return [
            LogKey::INTERNAL_ID          => (string) $this->getId(),
            LogKey::TYPE_KEY             => TransactionType::PRICE,
            LogKey::DATE_KEY             => $this->getDate()->format('Y-m-d'),
            LogKey::QUANTITY_KEY         => (float) $this->getAmount(),
            LogKey::PRODUCT_ID_KEY       => $this->getProduct() ? $this->getProduct()->getIdentifier() : '',
            LogKey::PARTNER_ID_KEY       => $this->getPartner() ? $this->getPartner()->getIdentifier() : '',
            LogKey::CREATED_AT_KEY       => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d H:i:s') : '',
            LogKey::UPDATED_AT_KEY       => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d H:i:s') : '',
            LogKey::TRANSACTION_ID_KEY   => $this->getTransaction() ? $this->getTransaction()->getTransactionId() : '',
            LogKey::CM_KEY               => $this->getPartner()->getChannelManager() ? $this->getPartner()->getChannelManager()->getIdentifier() : '',
        ];
    }
}
