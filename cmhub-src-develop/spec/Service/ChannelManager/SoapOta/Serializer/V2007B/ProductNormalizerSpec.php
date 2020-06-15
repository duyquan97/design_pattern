<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Service\ChannelManager\SoapOta\Serializer\V2007B\ProductNormalizer;
use App\Model\PartnerInterface;
use App\Model\ProductCollection;
use App\Model\ProductInterface;
use App\Model\Rate;
use App\Model\RatePlanCode;
use PhpSpec\ObjectBehavior;

class ProductNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductNormalizer::class);
    }

    function it_normalizes_products(ProductCollection $productCollection, PartnerInterface $partner, ProductInterface $product, ProductInterface $product1)
    {
        $productCollection->getPartner()->willReturn($partner);
        $partner->getIdentifier()->willReturn('partner_id');
        $productCollection->isEmpty()->willReturn(false);
        $productCollection->getProducts()->willReturn([
            $product,
            $product1
        ]);
        $product->getIdentifier()->willReturn('product_1');
        $product1->getIdentifier()->willReturn('product_2');
        $product->getName()->willReturn('name-1');
        $product1->getName()->willReturn('name-2');

        $this
            ->normalize($productCollection)
            ->shouldBe(
                [
                    'RoomStays' => [
                        [
                            'BasicPropertyInfo' => [
                                'HotelCode' => 'partner_id'
                            ]
                        ],
                        'RoomStay' => [
                            [
                                'RatePlans' => [
                                    'RatePlan' => [
                                        'RatePlanCode' => RatePlanCode::SBX,
                                        'RatePlanDescription' => [
                                            'Name' => Rate::SBX_RATE_PLAN_NAME
                                        ]
                                    ]
                                ],
                                'RoomTypes' => [
                                    [
                                        'RoomTypeCode'    => 'product_1',
                                        'RoomDescription' => [
                                            'Name' => 'name-1'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'RatePlans' => [
                                    'RatePlan' => [
                                        'RatePlanCode' => RatePlanCode::SBX,
                                        'RatePlanDescription' => [
                                            'Name' => Rate::SBX_RATE_PLAN_NAME
                                        ]
                                    ]
                                ],
                                'RoomTypes' => [
                                    [
                                        'RoomTypeCode'    => 'product_2',
                                        'RoomDescription' => [
                                            'Name' => 'name-2'
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ]
                ]
            );
    }

    function it_normalizes_when_product_collection_is_empty(ProductCollection $productCollection, PartnerInterface $partner)
    {
        $productCollection->getPartner()->willReturn($partner);
        $partner->getIdentifier()->willReturn('partner_id');
        $productCollection->isEmpty()->willReturn(true);

        $this
            ->normalize($productCollection)
            ->shouldBe(
                [
                    'RoomStays' => [
                        [
                            'BasicPropertyInfo' => [
                                'HotelCode' => 'partner_id'
                            ]
                        ],
                        'RoomStay' => []
                    ],
                ]);
    }
}
