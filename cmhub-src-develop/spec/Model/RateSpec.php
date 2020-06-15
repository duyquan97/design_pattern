<?php

namespace spec\App\Model;

use App\Model\ProductInterface;
use App\Model\Rate;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RateSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Rate::class);
    }

    function it_gets_sets_start(\DateTime $start)
    {
        $start->setTime(0, 0, 0)
            ->shouldBeCalled()
            ->willReturn($start);
        $this->setStart($start)->shouldBe($this);
        $this->getStart()->shouldBe($start);
    }

    function it_gets_sets_end(\DateTime $end)
    {
        $end->setTime(0, 0, 0)
            ->shouldBeCalled()
            ->willReturn($end);
        $this->setEnd($end)->shouldBe($this);
        $this->getEnd()->shouldBe($end);
    }

    function it_sets_gets_amount()
    {
        $this->setAmount(24.67)->shouldBe($this);
        $this->getAmount()->shouldBe(24.67);
    }

    function it_sets_gets_product(ProductInterface $product)
    {
        $this->setProduct($product);
        $this->getProduct()->shouldBe($product);
    }
}
