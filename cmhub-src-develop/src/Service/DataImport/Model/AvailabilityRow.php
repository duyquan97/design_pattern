<?php

namespace App\Service\DataImport\Model;

use App\Entity\Availability;

/**
 * Class AvailabilityRow
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityRow implements ImportDataRowInterface
{
    /**
     * @var string
     */
    private $productName;

    /**
     * @var Availability
     */
    private $availability;

    /**
     *
     * @var \Exception
     */
    private $exception;

    /**
     * @return string|null
     */
    public function getProductName(): ?string
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     *
     * @return self
     */
    public function setProductName(string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * @return Availability|mixed|null
     */
    public function getEntity()
    {
        return $this->getAvailability();
    }

    /**
     * @return Availability|null
     */
    public function getAvailability(): ?Availability
    {
        return $this->availability;
    }

    /**
     * @param Availability $availability
     *
     * @return self
     */
    public function setAvailability(Availability $availability): self
    {
        $this->availability = $availability;

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
     * @return self
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
    public function hasException(): bool
    {
        return $this->exception ? true : false;
    }
}
