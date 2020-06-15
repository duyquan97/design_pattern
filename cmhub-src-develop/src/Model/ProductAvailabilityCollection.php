<?php

namespace App\Model;

use App\Entity\Transaction;
use App\Entity\TransactionChannel;

/**
 * Class ProductAvailabilityCollection
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailabilityCollection extends AbstractWeekdayCollection implements ProductAvailabilityCollectionInterface, \Iterator
{
    /**
     *
     * @var ProductAvailabilityInterface[]
     */
    private $productAvailabilities = [];

    /**
     *
     * @var PartnerInterface
     */
    private $partner;

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
     * @var string
     */
    private $source = AvailabilitySource::CM;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * ProductAvailabilityCollection constructor.
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
     * @return ProductAvailabilityInterface[]
     */
    public function getProductAvailabilities(): array
    {
        return $this->productAvailabilities;
    }

    /**
     *
     * @param ProductAvailabilityInterface $productAvailability
     *
     * @return $this
     */
    public function addProductAvailability(ProductAvailabilityInterface $productAvailability): ProductAvailabilityCollectionInterface
    {
        $this->productAvailabilities[] = $productAvailability;

        return $this;
    }

    /**
     *
     * @param array $availabilities
     *
     * @return $this
     */
    public function addAvailabilities(array $availabilities)
    {
        foreach ($availabilities as $availability) {
            $this->addAvailability($availability);
        }

        return $this;
    }

    /**
     * @param AvailabilityInterface $availability
     *
     * @return $this
     */
    public function addAvailability(AvailabilityInterface $availability): ProductAvailabilityCollection
    {
        if (empty($this->weekdays)) {
            return $this->addAvailabilityWithoutCheckingWeekday($availability);
        }

        $startDate = clone $availability->getStart();
        $startDate->setTime(0, 0);
        $endDate = clone $availability->getEnd();
        $endDate->setTime(0, 0);

        while ($startDate <= $endDate) {
            if ($this->isDateInWeekdays($startDate)) {
                $newAvailability = clone $availability;
                $newAvailability->setStart(clone $startDate);
                $newAvailability->setEnd(clone $startDate);
                $this->addAvailabilityWithoutCheckingWeekday($newAvailability);
            }

            $startDate->modify('+1 day');
        }

        return $this;
    }

    /**
     *
     * @param ProductInterface $product
     *
     * @return ProductAvailabilityInterface
     */
    public function getProductAvailability(ProductInterface $product)
    {
        foreach ($this->productAvailabilities as $productAvailability) {
            if ($productAvailability->getProduct() === $product) {
                return $productAvailability;
            }
        }

        return new ProductAvailability($product);
    }

    /**
     *
     * @param ProductInterface $product
     *
     * @return int|null
     */
    public function getProductAvailabilityIndex(ProductInterface $product)
    {
        $index = 0;
        foreach ($this->productAvailabilities as $productAvailability) {
            if ($productAvailability->getProduct() === $product) {
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
     * @return AvailabilityInterface|null
     */
    public function getByProductAndDate(ProductInterface $product, \DateTime $date): ?AvailabilityInterface
    {
        foreach ($this->getProductAvailability($product)->getAvailabilities() as $availability) {
            if (($availability->getProduct() === $product) && ($availability->getStart()->format('Y-m-d') === $date->format('Y-m-d'))) {
                return $availability;
            }
        }

        return null;
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
     * @return ProductAvailabilityCollection
     */
    public function setPartner(PartnerInterface $partner): ProductAvailabilityCollection
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     *
     * @param Transaction $transaction
     *
     * @return ProductAvailabilityCollectionInterface
     */
    public function setTransaction(Transaction $transaction): ProductAvailabilityCollectionInterface
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     *
     * @return Transaction|null
     */
    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    /**
     *
     * @return ProductAvailabilityInterface|mixed
     */
    public function current()
    {
        return $this->productAvailabilities[$this->index];
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
     * @return int|mixed
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
        return isset($this->productAvailabilities[$this->key()]);
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
        return sizeof($this->productAvailabilities) === 0;
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
     * @return ProductAvailabilityCollection
     */
    public function setChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return ProductAvailabilityCollection
     */
    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getAvailabilities(): array
    {
        $availabilities = [];
        foreach ($this->productAvailabilities as $productAvailability) {
            $availabilities = array_merge($productAvailability->getAvailabilities(), $availabilities);
        }

        return $availabilities;
    }

    /**
     *
     * @param AvailabilityInterface $availability
     *
     * @return $this
     */
    private function addAvailabilityWithoutCheckingWeekday(AvailabilityInterface $availability)
    {
        $productAvailability = $this->getProductAvailability($availability->getProduct())->addAvailability($availability);
        $index = $this->getProductAvailabilityIndex($availability->getProduct());

        if (!is_null($index)) {
            $this->productAvailabilities[$index] = $productAvailability;

            return $this;
        }

        $this->productAvailabilities[] = $productAvailability->setProduct($availability->getProduct());

        return $this;
    }
}
