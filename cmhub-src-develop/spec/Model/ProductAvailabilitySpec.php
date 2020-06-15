<?php

namespace spec\App\Model;

use App\Entity\Product;
use App\Model\AvailabilityInterface;
use App\Model\PartnerInterface;
use App\Model\ProductAvailability;
use App\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAvailabilitySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAvailability::class);
    }

    function let(ProductInterface $product)
    {
        $this->beConstructedWith($product);
    }

    function it_gets_sets_and_adds_availabilities_attribute(AvailabilityInterface $availability, AvailabilityInterface $availability1, AvailabilityInterface $availability2, Product $product)
    {
        $availability->getStart()->willReturn(new \DateTime('-2 day'));
        $availability->getEnd()->willReturn(new \DateTime('-2 day'));

        $availability1->getStart()->willReturn(new \DateTime('-1 day'));
        $availability1->getEnd()->willReturn(new \DateTime('-1 day'));

        $availability2->getStart()->willReturn(new \DateTime());
        $availability2->getEnd()->willReturn(new \DateTime());
        $availability2->setProduct($product)->shouldBeCalledOnce();

        $this->setAvailabilities($availabilities = [$availability, $availability1]);
        $this->getAvailabilities()->shouldBe($availabilities);
        $this->addAvailability($availabilities[] = $availability2);
        $this->getAvailabilities()->shouldBe($availabilities);
    }

    function it_gets_and_sets_product_attribute(ProductInterface $product)
    {
        $this->setAvailabilities([]);
        $this->setProduct($product);
        $this->getProduct()->shouldBe($product);
    }

    function it_gets_and_sets_partner_attribute(PartnerInterface $partner)
    {
        $this->setPartner($partner);
        $this->getPartner()->shouldBe($partner);
    }
}
