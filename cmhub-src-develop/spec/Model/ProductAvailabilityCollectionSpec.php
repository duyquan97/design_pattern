<?php

namespace spec\App\Model;

use App\Entity\Product;
use App\Model\AvailabilityInterface;
use App\Model\PartnerInterface;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductAvailabilityInterface;
use App\Model\ProductInterface;
use PhpSpec\ObjectBehavior;

class ProductAvailabilityCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAvailabilityCollection::class);
    }

    function let(PartnerInterface $partner)
    {
        $this->beConstructedWith($partner);
    }

    function it_gets_and_adds_product_availabilities(ProductAvailabilityInterface $productAvailable)
    {
        $this->addProductAvailability($productAvailabilities[] = $productAvailable);
        $this->getProductAvailabilities()->shouldBe($productAvailabilities);
    }

    function it_gets_product_availability_fail(Product $product)
    {
        $this->getProductAvailability($product)->shouldBeAnInstanceOf(ProductAvailability::class);
    }

    function it_adds_availability(AvailabilityInterface $availability, ProductInterface $product, ProductAvailability $productAvailability)
    {
        $productAvailability->getProduct()->willReturn($product);
        $availability->getProduct()->willReturn($product);

        $productAvailability->addAvailability($availability)->willReturn($productAvailability);

        $this->addProductAvailability($productAvailability);

        $this->addAvailability($availability)->shouldBe($this);
    }

    function it_adds_availabilities(AvailabilityInterface $availability, ProductInterface $product, ProductAvailability $productAvailability)
    {
        $this->it_adds_availability($availability, $product, $productAvailability);

        $this->addAvailabilities([$availability, ])->shouldBe($this);
    }

    function it_gets_availability_by_product_and_date(ProductInterface $product, AvailabilityInterface $availability, ProductAvailabilityInterface $productAvailability)
    {
        $date = new \DateTime('2019-10-01');

        $this->addProductAvailability($productAvailability);

        $productAvailability->getProduct()->willReturn($product);
        $productAvailability->getAvailabilities()->shouldBeCalled()->willReturn([$availability]);

        $availability->getProduct()->willReturn($product);
        $availability->getStart()->willReturn(new \DateTime('2019-10-01'));

        $this->getByProductAndDate($product, $date)->shouldBe($availability);
    }

    function it_gets_availability_by_product_and_date_returns_null(ProductInterface $product, AvailabilityInterface $availability, ProductAvailabilityInterface $productAvailability)
    {
        $product->getIdentifier()->willReturn('320080');
        $date = new \DateTime('2019-10-01');

        $this->addProductAvailability($productAvailability);

        $productAvailability->getProduct()->willReturn($product);
        $productAvailability->getAvailabilities()->shouldBeCalled()->willReturn([$availability]);

        $availability->getProduct()->willReturn($product);
        $availability->getStart()->willReturn(new \DateTime('2019-10-02'));

        $this->getByProductAndDate($product, $date)->shouldBe(null);
    }

    function it_sets_and_gets_partner_attribute(PartnerInterface $partner)
    {
        $this->setPartner($partner)->shouldBe($this);
        $this->getPartner()->shouldBe($partner);
    }

    function it_returns_current_product_availability_collection(AvailabilityInterface $availability, ProductInterface $product, ProductAvailability $productAvailability, ProductAvailability $productAvailability1)
    {
        $this->it_adds_availability($availability, $product, $productAvailability);
        $this->current()->shouldBe($productAvailability);
        $this->it_adds_availability($availability, $product, $productAvailability1);
        $this->next();
        $this->current()->shouldBe($productAvailability1);
    }

    function it_returns_index_attribute()
    {
        $this->next();
        $this->key()->shouldBe(1);
    }

    function it_returns_true_if_valid_or_false_if_not_valid(AvailabilityInterface $availability, ProductInterface $product, ProductAvailability $productAvailability)
    {
        $this->valid()->shouldBe(false);
        $this->it_adds_availability($availability, $product, $productAvailability);
        $this->valid()->shouldBe(true);
    }

    function it_sets_index_as_zero()
    {
        $this->next();
        $this->next();
        $this->rewind();
        $this->key()->shouldBe(0);
    }
}
