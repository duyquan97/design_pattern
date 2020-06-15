<?php

namespace App\Model;

use App\Entity\Transaction;
use App\Entity\TransactionChannel;

/**
 * Class ProductRateCollection
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateCollection extends AbstractWeekdayCollection implements WeekdayCollectionInterface, ProductRateCollectionInterface, \Iterator
{
    /**
     *
     * @var ProductRateInterface[]
     */
    private $productRates = [];

    /**
     *
     * @var PartnerInterface
     */
    private $partner;

    /**
     *
     * @var Transaction
     */
    private $transaction;

    /**
     *
     * @var int
     */
    private $index = 0;

    /**
     * @var string
     */
    private $channel = TransactionChannel::EAI;

    /**
     * ProductRateCollection constructor.
     *
     * @param PartnerInterface $partner
     */
    public function __construct(PartnerInterface $partner)
    {
        parent::__construct();

        $this->partner = $partner;
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
     * @return ProductRateCollection
     */
    public function setPartner(PartnerInterface $partner): ProductRateCollection
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * @return array
     */
    public function getProductRates(): array
    {
        return $this->productRates;
    }

    /**
     *
     * @param ProductRateInterface[] $productRates
     *
     * @return ProductRateCollection
     */
    public function setProductRates(array $productRates): ProductRateCollection
    {
        $this->productRates = $productRates;

        return $this;
    }

    /**
     *
     * @param ProductRateInterface $productRate
     *
     * @return ProductRateCollection
     */
    public function addProductRate(ProductRateInterface $productRate): ProductRateCollection
    {
        foreach ($productRate->getRates() as $rate) {
            $this->addRate($productRate->getProduct(), $rate);
        }

        return $this;
    }

    /**
     *
     * @param ProductInterface $product
     * @param RateInterface    $rate
     *
     * @return $this
     */
    public function addRate(ProductInterface $product, RateInterface $rate)
    {
        if (empty($this->weekdays)) {
            return $this->addRateWithoutCheckingWeekday($product, $rate);
        }

        $startDate = clone $rate->getStart();
        $startDate->setTime(0, 0);
        $endDate = clone $rate->getEnd();
        $endDate->setTime(0, 0);

        while ($startDate <= $endDate) {
            if ($this->isDateInWeekdays($startDate)) {
                $newRate = clone $rate;
                $newRate->setStart(clone $startDate);
                $newRate->setEnd(clone $startDate);
                $this->addRateWithoutCheckingWeekday($product, $newRate);
            }

            $startDate->modify('+1 day');
        }

        return $this;
    }

    /**
     *
     * @param ProductInterface $product
     *
     * @return ProductRateInterface
     */
    public function getProductRate(ProductInterface $product): ProductRateInterface
    {
        if ($product) {
            foreach ($this->productRates as $productRate) {
                if ($productRate->getProduct() === $product) {
                    return $productRate;
                }
            }
        }

        return new ProductRate($product);
    }

    /**
     *
     * @param ProductInterface $product
     *
     * @return int|null
     */
    public function getProductRateIndex(ProductInterface $product)
    {
        $index = 0;
        foreach ($this->productRates as $productRate) {
            if ($product->getIdentifier() && $productRate->getProduct()->getIdentifier() === $product->getIdentifier()) {
                return $index;
            }

            $index++;
        }

        return null;
    }

    /**
     *
     * @param ProductInterface $product
     * @param \DateTime        $date
     *
     * @return RateInterface|null
     */
    public function getByProductAndDate(ProductInterface $product, \DateTime $date): ?RateInterface
    {
        foreach ($this->getProductRate($product)->getRates() as $rate) {
            if ($rate->getStart()->format('Y-m-d') === $date->format('Y-m-d')) {
                return $rate;
            }
        }

        return null;
    }

    /**
     *
     * @param Transaction $transaction
     *
     * @return $this
     */
    public function setTransaction(?Transaction $transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     *
     * @return Transaction
     */
    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    /**
     *
     * @return ProductRateInterface
     */
    public function current()
    {
        return $this->productRates[$this->index];
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
        return isset($this->productRates[$this->key()]);
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
    public function isEmpty(): bool
    {
        return sizeof($this->productRates) === 0;
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
     * @return ProductRateCollection
     */
    public function setChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     *
     * @param ProductInterface $product
     * @param RateInterface    $rate
     *
     * @return $this
     */
    private function addRateWithoutCheckingWeekday(ProductInterface $product, RateInterface $rate)
    {
        $productRate = $this->getProductRate($product)->addRate($rate);
        $index = $this->getProductRateIndex($product);

        if (!is_null($index)) {
            $this->productRates[$index] = $productRate;

            return $this;
        }

        $this->productRates[] = $productRate;

        return $this;
    }
}
