<?php

namespace spec\App\Service\ChannelManager\Wubook\Operation;

use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Entity\Product;
use App\Model\ProductCollection;
use App\Service\ChannelManager\Wubook\Operation\GetRoomsOperation;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;

class GetRoomsOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GetRoomsOperation::class);
    }

    function let(ProductLoader $productLoader, CmhubLogger $logger)
    {
        $this->beConstructedWith($productLoader, $logger);
    }

    function it_supports(){
        $this->supports('get_rooms')->shouldBe(true);
    }

    function it_gets_rooms(ProductLoader $productLoader, Partner $partner, ProductCollection $products, Product $product, Product $product1, ChannelManager $channelManager)
    {
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('abc123');
        $json = [
            "cm_auth" => [
                "username" => "wubook",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_rooms",
            "data" => [
                "data1" => "dataprovided1",
            ],
        ];
        $jsonString = json_encode($json);

        $productLoader->getByPartner($partner)->willReturn($products);
        $partner->getIdentifier()->willReturn('00145577');
        $product->getIdentifier()->willReturn('320080');
        $product->getName()->willReturn('room 1');
        $product1->getIdentifier()->willReturn('366455');
        $product1->getName()->willReturn('room 2');
        $products->toArray()->willReturn([$product, $product1]);
        $response = [
            "hotel_id" => "00145577",
            "rooms" => [
                [
                    "room_id" => '320080',
                    "name" => 'room 1'
                ],
                [
                    "room_id" => '366455',
                    "name" => 'room 2'
                ]
            ]
        ];

        $this->handle(json_decode($jsonString), $partner)->shouldBe($response);
    }
}
