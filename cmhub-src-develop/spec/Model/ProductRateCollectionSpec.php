<?php

namespace spec\App\Model;

use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Model\ProductRate;
use App\Model\ProductRateCollection;
use App\Model\ProductRateInterface;
use App\Model\RateInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductRateCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateCollection::class);
    }

    function let(PartnerInterface $partner)
    {
        $this->beConstructedWith($partner);
    }

    function it_sets_gets_partner(PartnerInterface $partner)
    {
        $this->setPartner($partner)->shouldBe($this);
        $this->getPartner()->shouldBe($partner);
    }

    function it_sets_gets_adds_product_rates(ProductRateInterface $productRate, ProductRateInterface $productRate1, ProductInterface $product, RateInterface $rate, RateInterface $rate1)
    {
        $rate->getStart()->willReturn(new \DateTime('2019-09-01'));
        $rate1->getStart()->willReturn(new \DateTime('2019-09-02'));
        $productRate->getProduct()->willReturn($product);
        $productRate1->getProduct()->willReturn($product);
        $productRate->getRates()->willReturn([$rate]);
        $productRate1->getRates()->willReturn([$rate1]);
        $product->getIdentifier()->willReturn('abx');
        $this->addProductRate($productRate);
        $this->addProductRate($productRate1);


        $this->getProductRates()->shouldHaveCount(1);
    }

    function it_adds_rate_and_product_when_index_is_not_null(ProductRate $productRate, ProductInterface $product, RateInterface $rate, ProductInterface $product1, ProductInterface $product2)
    {
        $product->getIdentifier()->willReturn('366455');
        $productRate->getProduct()->willReturn($product);

        $this->setProductRates([$productRate]);
        $productRate->addRate($rate)->shouldBeCalled()->willReturn($productRate);

        $this->addRate($product, $rate);


        $this->getProductRateIndex($product);
        $this->addRate($product1, $rate)->shouldBe($this);
    }

    function it_returns_same_product_as_parameter_at_getProductRate(ProductRate $productRate, ProductInterface $product)
    {
        $this->setProductRates([$productRate,]);
        $productRate->getProduct()
            ->shouldBeCalled()
            ->willReturn($product);
        $this->getProductRate($product)->shouldBe($productRate);
    }

    function it_returns_new_product_at_getProductRate(ProductInterface $product)
    {
        $this->getProductRate($product)->shouldBeAnInstanceOf(ProductRateInterface::class);
    }

    function it_adds_rate_and_product_to_collection(ProductRate $productRate, ProductInterface $product, RateInterface $rate, ProductInterface $product1)
    {
        $productRate->getProduct()->willReturn($product);

        $this->setProductRates([$productRate]);
        $productRate->addRate($rate)->shouldBeCalled()->willReturn($productRate);

        $this->addRate($product, $rate)->shouldBe($this);

        $this->addRate($product1, $rate)->shouldBe($this);
    }

    function it_gets_rate_by_product_and_date(ProductInterface $product, ProductRate $productRate, RateInterface $rate)
    {
        $date = new \DateTime('2019-10-01');

        $productRate->getProduct()->willReturn($product);
        $productRate->getRates()->shouldBeCalled()->willReturn([$rate]);
        $this->addProductRate($productRate);
        $productRate->getProduct()->shouldBeCalled()->willReturn($product);


        $rate->getStart()->shouldBeCalled()->willReturn(new \DateTime('2019-10-01'));

        $this->getByProductAndDate($product, $date)->shouldBe($rate);
    }

    function it_gets_rate_by_product_and_date_returns_null(ProductInterface $product, ProductRate $productRate, RateInterface $rate)
    {
        $date = new \DateTime('2019-10-01');
        $productRate->getProduct()->shouldBeCalled()->willReturn($product);
        $productRate->getRates()->shouldBeCalled()->willReturn([$rate]);
        $this->addProductRate($productRate);


        $rate->getStart()->shouldBeCalled()->willReturn(new \DateTime('2019-10-02'));

        $this->getByProductAndDate($product, $date)->shouldBe(null);
    }

    function it_returns_current_product_rate_collection(ProductRate $productRate, ProductRate $productRate1)
    {
        $this->setProductRates([$productRate, $productRate1]);
        $this->current()->shouldBe($productRate);
        $this->next();
        $this->current()->shouldBe($productRate1);
    }

    function it_returns_index_attribute()
    {
        $this->next();
        $this->key()->shouldBe(1);
    }

    function it_returns_true_if_valid_or_false_if_not_valid(ProductRate $productRate, ProductRate $productRate1)
    {
        $this->valid()->shouldBe(false);
        $this->setProductRates([$productRate, $productRate1]);
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
