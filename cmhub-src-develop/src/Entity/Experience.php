<?php

namespace App\Entity;

use App\Model\CommissionType;
use App\Model\ExperienceInterface;
use App\Utils\Monolog\LogKey;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Experience
 *
 * @ORM\Table(name="experience", indexes={@ORM\Index(name="ex_identifier_idx", columns={"identifier"})})
 * @ORM\Entity(repositoryClass="App\Repository\ExperienceRepository")
 *
 * @Serializer\ExclusionPolicy("all")
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Experience implements ExperienceInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", nullable=false, unique=true)
     *
     * @Serializer\Expose()
     */
    private $identifier;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Serializer\Expose()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     *
     * @Serializer\Expose()
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true, options={"default": 0})
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true, options={"default": 0})
     */
    private $commission;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $commissionType;

    /**
     * @var Partner
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Partner", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true,                          onDelete="CASCADE")
     *
     * @Assert\NotNull(message="The Partner code has not been found in database")
     */
    private $partner;

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
     * Experience constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @return self
     */
    public function setIdentifier(string $identifier): ExperienceInterface
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(?string $name): ExperienceInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     *
     * @param string $description
     *
     * @return Experience
     */
    public function setDescription(?string $description): Experience
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return self
     */
    public function setPrice(?float $price): ExperienceInterface
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getCommission(): ?float
    {
        return $this->commission;
    }

    /**
     * @param float $commission
     *
     * @return self
     */
    public function setCommission(?float $commission): ExperienceInterface
    {
        $this->commission = $commission;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommissionType(): ?string
    {
        return $this->commissionType;
    }

    /**
     * @param string $commissionType
     *
     * @return self
     */
    public function setCommissionType(?string $commissionType): ExperienceInterface
    {
        $this->commissionType = $commissionType;

        return $this;
    }

    /**
     * @return Partner|null
     */
    public function getPartner(): ?Partner
    {
        return $this->partner;
    }

    /**
     * @param Partner $partner
     *
     * @return self
     */
    public function setPartner(?Partner $partner): ExperienceInterface
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getCreatedAtFormatted(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt): ExperienceInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function getUpdatedAtFormatted(): string
    {
        return $this->updatedAt->format('Y-m-d H:i:s');
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(\DateTime $updatedAt): ExperienceInterface
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'                   => $this->getId(),
            'identifier'           => $this->getIdentifier(),
            'name'                 => $this->getName(),
            'price'                => $this->getPrice(),
            'commission_type'      => $this->getCommissionType(),
            'commission'           => $this->getCommission(),
            LogKey::PARTNER_ID_KEY => ($this->getPartner()) ? $this->getPartner()->getIdentifier() : null,
        ];
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s (%s)', $this->getName(), $this->getPartner()->getName()) ?: 'Experience name';
    }
}
