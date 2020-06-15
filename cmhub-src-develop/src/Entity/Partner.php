<?php

namespace App\Entity;

use App\Model\PartnerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Partner
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @ORM\Table(name="partner",indexes={@ORM\Index(name="par_identifier_idx", columns={"identifier"})})
 * @ORM\Entity(repositoryClass="App\Repository\PartnerRepository")
 *
 * @Serializer\ExclusionPolicy("all")
 *
 * @UniqueEntity("identifier")
 */
class Partner implements PartnerInterface
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
     * @var string
     *
     * @ORM\Column(type="string", nullable=false, unique=true)
     *
     * @Serializer\SerializedName("id")
     * @Serializer\Expose()
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $identifier;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Serializer\SerializedName("displayName")
     * @Serializer\Expose()
     */
    private $name;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Serializer\Expose()
     */
    private $description;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Serializer\Expose()
     */
    private $status = 'partner';

    /**
     *
     * @var ChannelManager
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ChannelManager")
     *
     * @ORM\JoinColumn(name="channel_manager_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $channelManager;

    /**
     *
     * @var string
     *
     * @Serializer\SerializedName("channelManagerHubApiKey")
     *
     * @Assert\NotNull(message="channelManagerHubApiKey is mandatory")
     */
    private $channelManagerHubApiKey;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="boolean")
     *
     * @Serializer\SerializedName("isChannelManagerEnabled")
     * @Serializer\Expose()
     */
    private $enabled = false;

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
     * @var CmUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CmUser", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="partner", fetch="EXTRA_LAZY")
     */
    private $products;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=8, nullable=false, options={"default": "EUR"})
     */
    private $currency = 'EUR';

    /**
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $connectedAt;

    /**
     *
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     *
     * @return null|string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     *
     * @param string $identifier
     *
     * @return Partner
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     *
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     *
     * @param null|string $name
     *
     * @return Partner
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     *
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     *
     * @param null|string $description
     *
     * @return Partner
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     *
     * @return ChannelManager|null
     */
    public function getChannelManager(): ?ChannelManager
    {
        return $this->channelManager;
    }

    /**
     *
     * @param ChannelManager $channelManager
     *
     * @return Partner
     */
    public function setChannelManager(?ChannelManager $channelManager): Partner
    {
        $this->channelManager = $channelManager;

        return $this;
    }

    /**
     *
     * @return null|string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     *
     * @param string $status
     *
     * @return Partner
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     *
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->getIdentifier();
    }

    /**
     *
     * @return string
     */
    public function getChannelManagerHubApiKey(): ?string
    {
        return $this->channelManagerHubApiKey;
    }

    /**
     *
     * @param string $channelManagerHubApiKey
     *
     * @return Partner
     */
    public function setChannelManagerHubApiKey(?string $channelManagerHubApiKey): Partner
    {
        $this->channelManagerHubApiKey = $channelManagerHubApiKey;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->channelManagerHubApiKey;
    }

    /**
     *
     * @param string $password
     *
     * @return Partner
     */
    public function setPassword($password): self
    {
        $this->channelManagerHubApiKey = $password;

        return $this;
    }

    /**
     * Set enabled
     *
     * @param string $enabled
     *
     * @return Partner
     */
    public function setEnabled($enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getEnabledString(): string
    {
        return $this->enabled ? 'Yes' : 'No';
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
     * @return string
     */
    public function getCreatedAtFormatted(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    /**
     *
     * @param \DateTime $createdAt
     *
     * @return Partner
     */
    public function setCreatedAt(\DateTime $createdAt): Partner
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
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
     *
     * @param \DateTime $updatedAt
     *
     * @return Partner
     */
    public function setUpdatedAt(\DateTime $updatedAt): Partner
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     *
     * @return CmUser
     */
    public function getUser(): ?CmUser
    {
        return $this->user;
    }

    /**
     *
     * @param CmUser $user
     *
     * @return Partner
     */
    public function setUser(?CmUser $user): Partner
    {
        $this->user = $user;

        return $this;
    }

    /**
     *
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->products->toArray();
    }

    /**
     *
     * @param Product[] $products
     *
     * @return Partner
     */
    public function setProducts(array $products): Partner
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return Partner
     */
    public function setCurrency(string $currency): Partner
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getConnectedAt(): ?\DateTime
    {
        return $this->connectedAt;
    }

    /**
     * @param \DateTime|null $connectedAt
     *
     * @return Partner
     */
    public function setConnectedAt(?\DateTime $connectedAt): Partner
    {
        $this->connectedAt = $connectedAt;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'status' => $this->getStatus(),
            'channel' => ($this->getChannelManager()) ? $this->getChannelManager()->toArray() : [],
            'user' => ($this->getUser()) ? $this->getUser()->toArray() : [],
        ];
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s (%s)', $this->getName(), $this->getIdentifier()) ?: 'Partner';
    }
}
