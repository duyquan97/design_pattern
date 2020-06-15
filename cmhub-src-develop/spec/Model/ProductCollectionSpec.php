<?php

namespace spec\App\Model;

use App\Model\PartnerInterface;
use App\Model\ProductCollection;
use App\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductCollection::class);
    }

    function let(PartnerInterface $partner)
    {
        $this->beConstructedWith($partner);
    }

    function it_returns_array_and_adds_product(ProductInterface $product)
    {
        $this->addProduct($products[] = $product);
        $this->toArray()->shouldBe($products);
    }

    function it_gets_and_sets_partner(PartnerInterface $partner)
    {
        $this->setPartner($partner);
        $this->getPartner()->shouldBe($partner);
    }

    function it_adds_index_returns_current_and_index(ProductInterface $product, ProductInterface $product1)
    {
        $product->getIdentifier()->willReturn('A');
        $product1->getIdentifier()->willReturn('B');
        $this->addProduct($products[] = $product);
        $this->next();
        $this->key()->shouldBe(1);
        $this->addProduct($products[] = $product1);
        $this->current()->shouldBe($products[1]);
    }

    function it_returns_true_if_valid_or_false_if_not_valid(ProductInterface $product, ProductInterface $product1)
    {
        $product->getIdentifier()->willReturn('A');
        $product1->getIdentifier()->willReturn('B');
        $this->addProduct($products[] = $product);
        $this->next();
        $this->key()->shouldBe(1);
        $this->valid()->shouldBe(false);
        $this->addProduct($products[] = $product1);
        $this->valid()->shouldBe(true);
    }

    function it_sets_index_as_zero()
    {
        $this->next();
        $this->next();
        $this->rewind();
        $this->key()->shouldBe(0);
    }

    function it_returns_true_if_it_is_empty(ProductInterface $product)
    {
        $this->isEmpty()->shouldBe(true);
        $this->addProduct($product);
        $this->isEmpty()->shouldBe(false);
    }
}
