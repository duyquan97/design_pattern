<?php

namespace App\Model;

/**
 * Class ProductAvailability
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailability implements ProductAvailabilityInterface
{
    /**
     *
     * @var AvailabilityInterface[]
     */
    private $availabilities;

    /**
     *
     * @var ProductInterface
     */
    private $product;

    /**
     *
     * @var PartnerInterface
     */
    private $partner;

    /**
     * ProductAvailability constructor.
     *
     * @param ProductInterface $product
     */
    public function __construct(ProductInterface $product)
    {
        $this->product = $product;
        $this->availabilities = [];
    }

    /**
     *
     * @return AvailabilityInterface[]
     */
    public function getAvailabilities(): array
    {
        return $this->availabilities;
    }

    /**
     *
     * @param AvailabilityInterface[] $availabilities
     *
     * @return ProductAvailability
     */
    public function setAvailabilities(array $availabilities): ProductAvailability
    {
        $this->availabilities = $availabilities;

        return $this;
    }

    /**
     *
     * @param AvailabilityInterface $availability
     *
     * @return ProductAvailability
     */
    public function addAvailability(AvailabilityInterface $availability): ProductAvailability
    {
        if ($availability->getStart()->format('Y-m-d') === $availability->getEnd()->format('Y-m-d')) {
            return $this->addSingleDayAvailability($availability);
        }

        $startDate = clone $availability->getStart();
        $startDate->setTime(0, 0);
        $endDate = clone $availability->getEnd();
        $endDate->setTime(0, 0);

        while ($startDate <= $endDate) {
            $newAvailability = clone $availability;
            $newAvailability->setStart(clone $startDate);
            $newAvailability->setEnd(clone $startDate);

            $this->addSingleDayAvailability($newAvailability);

            $startDate->modify('+1 day');
        }

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return AvailabilityInterface|null
     */
    public function getAvailabilityByDate(\DateTime $date): ?AvailabilityInterface
    {
        /** @var Availability $item */
        foreach ($this->availabilities as $item) {
            if ($date->format('Y-m-d') === $item->getStart()->format('Y-m-d')
            ) {
                return $item;
            }
        }

        return null;
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
     * @return ProductAvailability
     */
    public function setProduct(ProductInterface $product): ProductAvailability
    {
        $this->product = $product;
        $availabilities = [];

        foreach ($this->availabilities as $availability) {
            $availabilities[] = (clone $availability)->setProduct($product);
        }

        $this->availabilities = $availabilities;

        return $this;
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
     * @return ProductAvailability
     */
    public function setPartner(PartnerInterface $partner): ProductAvailability
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     *
     * @return ProductAvailability
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
        return sizeof($this->availabilities) === 0;
    }

    /**
     * @param AvailabilityInterface $availability
     *
     * @return ProductAvailability
     */
    private function addSingleDayAvailability(AvailabilityInterface $availability): ProductAvailability
    {
        $availability->setProduct($this->product);

        $existed = false;
        /**
         * @var AvailabilityInterface $item
         */
        foreach ($this->getAvailabilities() as $index => $item) {
            if ($availability->getStart()->format('Y-m-d') === $item->getStart()->format('Y-m-d')) {
                if (!$availability->getStock() && $item->getStock()) {
                    $availability->setStock($item->getStock());
                }

                $this->availabilities[$index] = $availability;
                $existed = true;
            }
        }

        if (!$existed) {
            $this->availabilities[] = $availability;
        }

        return $this;
    }
}
