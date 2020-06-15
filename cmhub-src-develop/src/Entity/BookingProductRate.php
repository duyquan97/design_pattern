<?php

namespace App\Entity;

use App\Model\ProductInterface;
use App\Model\RateInterface;
use App\Model\Rate as RateModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * Rate
 *
 * @ORM\Table(name="booking_product_rates")
 * @ORM\Entity(repositoryClass="App\Repository\BookingProductRateRepository")
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProductRate extends RateModel
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
     * @ORM\ManyToOne(targetEntity="App\Entity\BookingProduct", inversedBy="rates")
     * @ORM\JoinColumn(nullable=true,                                 onDelete="CASCADE")
     */
    private $bookingProduct;

    /**
     *
     * @var float
     *
     * @ORM\Column(type="float")
     */
    protected $amount = 0;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string", length=3)
     */
    protected $currency;

    /**
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * BookingProductRate constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get bookingProduct
     *
     * @return BookingProduct
     */
    public function getBookingProduct(): ?BookingProduct
    {
        return $this->bookingProduct;
    }

    /**
     * Set bookingProduct
     *
     * @param BookingProduct $bookingProduct
     *
     * @return BookingProductRate
     */
    public function setBookingProduct(BookingProduct $bookingProduct): self
    {
        $this->bookingProduct = $bookingProduct;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return BookingProductRate
     */
    public function setDate(?\DateTime $date): RateInterface
    {
        $this->date = $date;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getStart(): \DateTime
    {
        return $this->date;
    }

    /**
     *
     * @return \DateTime
     */
    public function getEnd(): \DateTime
    {
        return $this->date;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getAmount();
    }

    /**
     *
     * @return ProductInterface|null
     */
    public function getProduct(): ?ProductInterface
    {
        return $this->getBookingProduct() ? $this->getBookingProduct()->getProduct() : null;
    }
}
