<?php

namespace spec\App\Model;

use App\Entity\Product;
use App\Model\ProductInterface;
use App\Model\ProductRate;
use App\Model\Rate;
use App\Model\RateInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductRateSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRate::class);
    }

    function let(ProductInterface $product)
    {
        $this->beConstructedWith($product);
    }

    function it_gets_sets_adds_rates(RateInterface $rate, RateInterface $rate1)
    {
        $rate->getStart()->willReturn(new \DateTime('2019-09-02'));
        $this->setRates($rates = [$rate, ]);
        $this->getRates()->shouldBe($rates);
        $rate1->getStart()->willReturn(new \DateTime('2019-09-01'));
        $this->addRate($rates[] = $rate1);
        $this->getRates()->shouldBe($rates);
    }

    function it_gets_sets_product(Product $product)
    {
        $this->setRates([]);
        $this->setProduct($product);
        $this->getProduct()->shouldBe($product);
    }

    function it_overwrite_rate(Rate $rate, Rate $rate1, Rate $rate2)
    {
        $rate->getStart()->willReturn(new \DateTime('2019-09-01'));
        $rate1->getStart()->willReturn(new \DateTime('2019-09-01'));
        $rate2->getStart()->willReturn(new \DateTime('2019-09-02'));
        $rate->getAmount()->shouldBeCalled()->willReturn(3);
        $rate1->setAmount(3)->shouldBeCalled();

        $this->setRates([$rate1, $rate2]);
        $this->addRate($rate);
        $this->getRates()->shouldHaveCount(2);
    }

    function it_add_rate(Rate $rate, Rate $rate1, Rate $rate2)
    {
        $rate->getStart()->willReturn(new \DateTime('2019-09-01'));
        $rate1->getStart()->willReturn(new \DateTime('2019-09-02'));
        $rate2->getStart()->willReturn(new \DateTime('2019-09-03'));

        $this->setRates([$rate1, $rate2]);
        $this->addRate($rate);

        $this->getRates()->shouldHaveCount(3);
    }
}
