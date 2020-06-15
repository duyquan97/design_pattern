<?php

namespace spec\App\Service\ChannelManager\BB8\Serializer;

use App\Entity\Partner;
use App\Entity\Product;
use App\Model\ProductCollection;
use App\Service\ChannelManager\BB8\Serializer\ProductCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Intl\Exception\NotImplementedException;

class ProductCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductCollectionNormalizer::class);
    }

    function it_normalize(
        ProductCollection $collection,
        Product $product1,
        Product $product2,
        Partner $partner1,
        Partner $partner2
    ) {
        $expected = [
            [
                'title' => 'name_1',
                'isSellable' => true,
                'isReservable' => true,
                'externalPartnerId' => 'partner_id',
                'externalId' => 'product_1',
                'description' => 'description_1',
                'isMaster' => true,
                'externalCreatedAt' => '2019-01-01T00:00:00+00:00',
                'externalUpdatedAt' => '2019-01-01T00:00:00+00:00',
            ],
            [
                'title' => 'name_2',
                'isSellable' => false,
                'isReservable' => true,
                'externalPartnerId' => 'partner_id',
                'externalId' => 'product_2',
                'description' => 'description_2',
                'isMaster' => false,
                'externalCreatedAt' => '2019-01-01T00:00:00+00:00',
                'externalUpdatedAt' => '2019-01-01T00:00:00+00:00',
            ],
        ];

        $collection->getProducts()->shouldBeCalled()->willReturn([$product1, $product2]);
        $product1->getPartner()->shouldBeCalled()->willReturn($partner1);
        $partner1->getIdentifier()->shouldBeCalled()->willReturn('partner_id');

        $product1->getCreatedAt()->willReturn(new \DateTime('2019-01-01', new \DateTimeZone('UTC')));
        $product1->getUpdatedAt()->willReturn(new \DateTime('2019-01-01', new \DateTimeZone('UTC')));
        $product1->getName()->willReturn('name_1');
        $product1->isReservable()->shouldBeCalled()->willReturn(true);
        $product1->isSellable()->shouldBeCalled()->willReturn(true);
        $product1->isMaster()->shouldBeCalled()->willReturn(true);
        $product1->getIdentifier()->willReturn('product_1');
        $product1->getDescription()->willReturn('description_1');

        $product2->getPartner()->shouldBeCalled()->willReturn($partner2);
        $partner2->getIdentifier()->shouldBeCalled()->willReturn('partner_id');
        $product2->getCreatedAt()->willReturn(new \DateTime('2019-01-01', new \DateTimeZone('UTC')));
        $product2->getUpdatedAt()->willReturn(new \DateTime('2019-01-01', new \DateTimeZone('UTC')));
        $product2->getName()->willReturn('name_2');
        $product2->isSellable()->shouldBeCalled()->willReturn(false);
        $product2->isMaster()->shouldBeCalled()->willReturn(false);
        $product2->isReservable()->shouldBeCalled()->willReturn(true);
        $product2->getIdentifier()->willReturn('product_2');
        $product2->getDescription()->willReturn('description_2');

        $this->normalize($collection)->shouldBe($expected);
    }

    function it_support_normalization()
    {
        $this->supportsNormalization(ProductCollection::class)->shouldBe(true);
    }

    function it_does_not_support_denormalization()
    {
        $this->supportsDenormalization(Argument::type('string'))->shouldBe(false);
    }

    function it_throw_exception_when_denormalize()
    {
        $this->shouldThrow(NotImplementedException::class)->during('denormalize', [Argument::type('array')]);
    }
}
