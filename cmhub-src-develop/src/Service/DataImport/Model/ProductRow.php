<?php

namespace App\Service\DataImport\Model;

use App\Entity\Partner;
use App\Model\ProductInterface;

/**
 * Class Row
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRow implements ImportDataRowInterface
{
    /**
     *
     * @var ProductInterface
     */
    private $product;

    /**
     *
     * @var Partner
     */
    private $partner;

    /**
     *
     * @var \Exception
     */
    private $exception;

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
     * @return ProductRow
     */
    public function setPartner(Partner $partner): ProductRow
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * @return ProductInterface|mixed|null
     */
    public function getEntity()
    {
        return $this->getProduct();
    }

    /**
     *
     * @return ProductInterface
     */
    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    /**
     *
     * @param ProductInterface $product
     *
     * @return ProductRow
     */
    public function setProduct(ProductInterface $product): ProductRow
    {
        $this->product = $product;

        return $this;
    }

    /**
     *
     * @return \Exception
     */
    public function getException(): ?\Exception
    {
        return $this->exception;
    }

    /**
     *
     * @param \Exception $exception
     *
     * @return ProductRow
     */
    public function setException(\Exception $exception): ImportDataRowInterface
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function hasProduct()
    {
        return $this->product ? true : false;
    }

    /**
     *
     * @return bool
     */
    public function hasException(): bool
    {
        return $this->exception ? true : false;
    }
}
