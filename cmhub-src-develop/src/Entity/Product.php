<?php

namespace App\Entity;

use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Utils\Monolog\LogKey;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @ORM\Table(name="product",indexes={@ORM\Index(name="pro_identifier_idx", columns={"identifier"})})
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 *
 * @Serializer\ExclusionPolicy("all")
 *
 * @UniqueEntity("identifier")
 */
class Product implements ProductInterface
{
    /**
     *
     * @var string
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
     * @Serializer\SerializedName("productCode")
     * @Serializer\Expose()
     */
    private $identifier;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Serializer\SerializedName("productName")
     * @Serializer\Expose()
     */
    private $name;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Serializer\SerializedName("productBrief")
     * @Serializer\Expose()
     */
    private $description;

    /**
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     *
     * @Serializer\SerializedName("isSellable")
     * @Serializer\Expose()
     */
    private $sellable = false;

    /**
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     *
     * @Serializer\SerializedName("isReservable")
     * @Serializer\Expose()
     */
    private $reservable = true;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Partner", inversedBy="products", cascade={"persist"}, fetch="EXTRA_LAZY")
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
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="masterProduct", cascade={"persist"})
     */
    private $linkedProducts;

    /**
     *
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="linkedProducts")
     */
    private $masterProduct;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->linkedProducts = new ArrayCollection();
    }

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
     * @return string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     *
     * @param string $identifier
     *
     * @return Product
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName(string $name): self
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
     * @return Product
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isSellable()
    {
        return $this->sellable;
    }

    /**
     * @return string
     */
    public function getSellableAsString()
    {
        return $this->sellable ? 'Yes' : 'No';
    }

    /**
     *
     * @param bool $sellable
     *
     * @return Product
     */
    public function setSellable(bool $sellable): self
    {
        $this->sellable = $sellable;

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isReservable(): bool
    {
        return $this->reservable;
    }

    /**
     * @return string
     */
    public function getReservableAsString()
    {
        return $this->reservable ? 'Yes' : 'No';
    }

    /**
     *
     * @param bool $reservable
     *
     * @return Product
     */
    public function setReservable(bool $reservable): self
    {
        $this->reservable = $reservable;

        return $this;
    }

    /**
     *
     * @return Partner|null
     */
    public function getPartner(): ?PartnerInterface
    {
        return $this->partner;
    }

    /**
     *
     * @param Partner $partner
     *
     * @return Product
     */
    public function setPartner(?Partner $partner): ProductInterface
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSellable() && $this->isReservable();
    }

    /**
     *
     * @Serializer\VirtualProperty()
     * @Serializer\Expose()
     * @Serializer\SerializedName("partnerCode")
     *
     * @return null|string
     */
    public function getPartnerCode()
    {
        return $this->getPartner()->getIdentifier();
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
     * @return Product
     */
    public function setCreatedAt(\DateTime $createdAt): Product
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
     * @return Product
     */
    public function setUpdatedAt(\DateTime $updatedAt): Product
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     *
     * @return Collection
     */
    public function getLinkedProducts()
    {
        return $this->linkedProducts;
    }

    /**
     * @return string
     */
    public function getLinkedProductsName(): string
    {
        $names = [];
        /** @var Product $product */
        foreach ($this->linkedProducts as $product) {
            $names[] = $product->getName();
        }

        return implode(',', $names);
    }

    /**
     *
     * @return bool
     */
    public function isMaster(): bool
    {
        if ($this->masterProduct) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param Product[] $linkedProducts
     *
     * @return Product
     */
    public function setLinkedProducts($linkedProducts): Product
    {
        foreach ($linkedProducts as $product) {
            $product->setMasterProduct($this);
        }

        $this->linkedProducts = $linkedProducts;

        return $this;
    }

    /**
     *
     * @param Product $linkedProduct
     *
     * @return $this
     */
    public function addLinkedProduct(Product $linkedProduct)
    {
        $linkedProduct->setMasterProduct($this);

        return $this;
    }

    /**
     *
     * @param Product $linkedProduct
     *
     * @return $this
     */
    public function removeLinkedProduct(Product $linkedProduct)
    {
        $linkedProduct->setMasterProduct(null);

        return $this;
    }

    /**
     *
     * @return ProductInterface
     */
    public function getMasterProduct(): ?ProductInterface
    {
        return $this->masterProduct;
    }

    /**
     *
     * @param Product $masterProduct
     *
     * @return Product
     */
    public function setMasterProduct(?Product $masterProduct): Product
    {
        $this->masterProduct = $masterProduct;

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function hasLinkedProducts(): bool
    {
        if (sizeof($this->getLinkedProducts()) > 0) {
            return true;
        }

        return false;
    }

    /**
     *
     * @return bool
     */
    public function hasMasterProduct()
    {
        if ($this->masterProduct) {
            return true;
        }

        return false;
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
            LogKey::PARTNER_ID_KEY => $this->getPartner()->getIdentifier(),
        ];
    }

    /**
     * @return string
     */
    public function isChained(): string
    {
        if ($this->hasMasterProduct() || $this->hasLinkedProducts()) {
            return 'Yes';
        }

        return 'No';
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s (%s)', $this->getName(), $this->getIdentifier()) ?: 'Empty name';
    }
}
