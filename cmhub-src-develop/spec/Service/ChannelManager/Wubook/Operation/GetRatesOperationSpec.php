<?php

namespace spec\App\Service\ChannelManager\Wubook\Operation;

use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Entity\Product;
use App\Model\ProductCollection;
use App\Model\Rate;
use App\Model\RatePlanCode;
use App\Service\ChannelManager\Wubook\Operation\GetRatesOperation;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;

class GetRatesOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GetRatesOperation::class);
    }

    function let(ProductLoader $productLoader, CmhubLogger $logger)
    {
        $this->beConstructedWith($productLoader, $logger);
    }

    function it_supports(){
        $this->supports('get_rates')->shouldBe(true);
    }

    function it_gets_rates(ProductLoader $productLoader, Partner $partner, ProductCollection $products, Product $product, Product $product1, ChannelManager $channelManager)
    {
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('abc123');
        $json = [
            "cm_auth" => [
                "username" => "yieldplanet",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00289056",
            ],
            "action" => "get_rates",
            "data" => [
                "data1" => "dataprovided1",
            ],
        ];
        $jsonString = json_encode($json);

        $productLoader->getByPartner($partner)->willReturn($products);
        $partner->getIdentifier()->willReturn('00289056');
        $partner->getCurrency()->willReturn('EUR');
        $product->getIdentifier()->willReturn('722679');
        $product1->getIdentifier()->willReturn('722681');
        $products->toArray()->willReturn([$product, $product1]);
        $response = [
            "hotel_id" => "00289056",
            "rates" => [
                [
                    "rate_id" => RatePlanCode::SBX,
                    "name" => Rate::SBX_RATE_PLAN_NAME,
                    "currency" => "EUR",
                    "rooms" => ['722679', '722681']
                ]
            ]
        ];
        $this->handle(json_decode($jsonString), $partner)->shouldBe($response);
    }
}
