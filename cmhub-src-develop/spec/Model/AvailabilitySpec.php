<?php

namespace spec\App\Model;

use App\Model\Availability;
use App\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AvailabilitySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Availability::class);
    }

    function let(ProductInterface $product)
    {
        $this->beConstructedWith($product);
    }

    function it_gets_and_sets_startDate_attribute(\DateTime $startDate)
    {
        $startDate->setTime(0, 0, 0)
            ->shouldBeCalled()
            ->willReturn($startDate);
        $this->setStart($startDate);
        $this->getStart()->shouldBe($startDate);
    }

    function it_gets_and_sets_endDate_attribute(\DateTime $endDate)
    {
        $endDate->setTime(0, 0, 0)
            ->shouldBeCalled()
            ->willReturn($endDate);
        $this->setEnd($endDate);
        $this->getEnd()->shouldBe($endDate);
    }

    function it_gets_and_sets_stock()
    {
        $this->setStock(5);
        $this->getStock()->shouldBe(5);
    }

    function it_gets_and_sets_product(ProductInterface $product)
    {
        $this->setProduct($product);
        $this->getProduct()->shouldBe($product);
    }
}
