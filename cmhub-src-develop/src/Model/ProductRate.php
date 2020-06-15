<?php

namespace App\Model;

/**
 * Class ProductRate
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRate implements ProductRateInterface
{
    /**
     *
     * @var Rate[]
     */
    private $rates;

    /**
     *
     * @var ProductInterface
     */
    private $product;

    /**
     * ProductRate constructor.
     *
     * @param ProductInterface $product
     */
    public function __construct(ProductInterface $product)
    {
        $this->product = $product;
        $this->rates = [];
    }

    /**
     *
     * @return Rate[]
     */
    public function getRates(): array
    {
        return $this->rates;
    }

    /**
     *
     * @param Rate[] $rates
     *
     * @return ProductRate
     */
    public function setRates(array $rates): ProductRate
    {
        $this->rates = $rates;

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return RateInterface|null
     */
    public function getRateByDate(\DateTime $date): ?RateInterface
    {
        /** @var Rate $item */
        foreach ($this->rates as $item) {
            if ($date->format('Y-m-d') === $item->getStart()->format('Y-m-d')) {
                return $item;
            }
        }

        return null;
    }

    /**
     *
     * @param RateInterface $rate
     *
     * @return ProductRate
     */
    public function addRate(RateInterface $rate): ProductRate
    {
        /** @var Rate $item */
        foreach ($this->rates as $index => $item) {
            if ($rate->getStart()->format('Y-m-d') === $item->getStart()->format('Y-m-d')) {
                $this->rates[$index]->setAmount($rate->getAmount());

                return $this;
            }
        }

        $this->rates[] = $rate;

        return $this;
    }

    /**
     *
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    /**
     *
     * @param ProductInterface $product
     *
     * @return ProductRate
     */
    public function setProduct(ProductInterface $product): ProductRate
    {
        $this->product = $product;
        $rates = [];

        foreach ($this->rates as $rate) {
            $rates[] = (clone $rate)->setProduct($product);
        }

        $this->rates = $rates;

        return $this;
    }

    /**
     *
     * @return ProductRate
     */
    public function cloneInstance()
    {
        return clone $this;
    }

    /**
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return sizeof($this->rates) === 0;
    }
}
