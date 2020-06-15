<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2016A;

use App\Model\ProductCollection;
use App\Model\ProductInterface;
use App\Service\ChannelManager\SoapOta\Serializer\V2016A\ProductCollectionNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class ProductCollectionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductCollectionNormalizer::class);
    }

    function let()
    {
        $this->beConstructedWith();
    }

    function it_supports_normalization()
    {
        $this->supportsNormalization(ProductCollection::class)->shouldBe(true);
    }

    function it_doesnt_support_denormalization()
    {
        $this->supportsDenormalization(ProductCollection::class)->shouldBe(false);
    }

    function it_doesnt_denormalize()
    {
        $this->shouldThrow(MethodNotImplementedException::class)->during('denormalize', [["patata" => "pepino"]]);
    }

    function it_normalizes(ProductCollection $productCollection, ProductInterface $product, ProductInterface $product1)
    {
        $productCollection->toArray()->willReturn([$product, $product1]);
        $product->getIdentifier()->willReturn('1');
        $product->getName()->willReturn('supercool room');

        $product1->getIdentifier()->willReturn('2');
        $product1->getName()->willReturn('poor room');

        $response = [
            "GuestRooms" => [
                'GuestRoom' => [
                    [
                        "Code" => "1",
                        "TypeRoom" => [
                            "Name" => "supercool room",
                        ]
                    ],
                    [
                        "Code" => "2",
                        "TypeRoom" => [
                            "Name" => "poor room",
                        ]
                    ]
                ]
            ],
        ];

        $this->normalize($productCollection)->shouldBeLike($response);
    }
}
