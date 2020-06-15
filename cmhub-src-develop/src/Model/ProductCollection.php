<?php

namespace App\Model;

/**
 * Class ProductCollection
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductCollection implements \Iterator
{

    /**
     *
     * @var ProductInterface[]
     */
    private $products;

    /**
     *
     * @var PartnerInterface
     */
    private $partner;

    /**
     *
     * @var int
     */
    private $index;

    /**
     * ProductCollection constructor.
     *
     * @param PartnerInterface $partner
     * @param array            $products
     */
    public function __construct(PartnerInterface $partner = null, array $products = array())
    {
        $this->partner = $partner;
        $this->products = $products;
    }

    /**
     *
     * @param ProductInterface $product
     *
     * @return $this
     */
    public function addProduct(ProductInterface $product): ProductCollection
    {
        foreach ($this->products as $existingProduct) {
            if ($existingProduct->getIdentifier() === $product->getIdentifier()) {
                return $this;
            }
        }

        $this->products[] = $product;

        return $this;
    }

    /**
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function contains(ProductInterface $product): bool
    {
        foreach ($this->products as $existingProduct) {
            if ($existingProduct->getIdentifier() === $product->getIdentifier()) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @return PartnerInterface
     */
    public function getPartner(): PartnerInterface
    {
        return $this->partner;
    }

    /**
     *
     * @param PartnerInterface $partner
     *
     * @return ProductCollection
     */
    public function setPartner(PartnerInterface $partner): ProductCollection
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     *
     * @return ProductInterface[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     *
     * @param ProductInterface[] $products
     *
     * @return ProductCollection
     */
    public function setProducts(array $products): ProductCollection
    {
        $this->products = $products;

        return $this;
    }

    /**
     *
     * @return ProductInterface
     */
    public function current()
    {
        return $this->products[$this->index];
    }

    /**
     *
     * @return void
     */
    public function next()
    {
        $this->index++;
    }

    /**
     *
     * @return int
     */
    public function key()
    {
        return $this->index;
    }

    /**
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->products[$this->key()]);
    }

    /**
     *
     * @return void
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     *
     * @return bool
     */
    public function isEmpty()
    {
        if (!sizeof($this->products) > 0) {
            return true;
        }

        return false;
    }

    /**
     *
     * @return ProductInterface[]|array
     */
    public function toArray()
    {
        return $this->products;
    }
}
