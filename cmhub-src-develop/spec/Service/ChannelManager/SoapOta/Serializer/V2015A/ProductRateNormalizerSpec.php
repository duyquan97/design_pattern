<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2015A;

use App\Entity\Partner;
use App\Entity\Product;
use App\Model\ProductRate;
use App\Model\ProductRateCollection;
use App\Model\Rate;
use App\Model\RatePlanCode;
use App\Service\ChannelManager\SoapOta\Serializer\V2015A\ProductRateNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

class ProductRateNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateNormalizer::class);
    }

    function it_does_not_supports_denormalization()
    {
        $this->supportsDenormalization(Argument::type('string'))->shouldBe(false);
    }

    function it_support_normalization()
    {
        $this->supportsNormalization(ProductRateCollection::class)->shouldBe(true);
    }

    function it_doesnt_denormalize()
    {
        $this->shouldThrow(MethodNotImplementedException::class)->during('denormalize', ['hola']);
    }

    function it_normalizes(ProductRateCollection $productRateCollection, Partner $partner, ProductRate $productRate1,
       ProductRate $productRate2, Rate $rate1, Rate $rate2, Rate $rate3, Product $product1, Product $product2)
    {
        $response = [
            'RatePlans' => [
                'HotelCode' => 'partner_id',
                'RatePlan'  => [
                    [
                        'RatePlanCode' => RatePlanCode::SBX,
                        'Start'        => '2019-10-01',
                        'End'          => '2019-10-01',
                        'Rates'        => [
                            'Rate' => [
                                [
                                    'InvTypeCode'     => 'product_id_1',
                                    'CurrencyCode'    => 'EUR',
                                    'BaseByGuestAmts' => [
                                        'BaseByGuestAmt' => [
                                            [
                                                'AmountAfterTax' => 1,
                                                'NumberOfGuests' => 2
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'RatePlanCode' => RatePlanCode::SBX,
                        'Start'        => '2019-10-02',
                        'End'          => '2019-10-02',
                        'Rates'        => [
                            'Rate' => [
                                [
                                    'InvTypeCode'     => 'product_id_2',
                                    'CurrencyCode'    => 'EUR',
                                    'BaseByGuestAmts' => [
                                        'BaseByGuestAmt' => [
                                            [
                                                'AmountAfterTax' => 2,
                                                'NumberOfGuests' => 2
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'RatePlanCode' => RatePlanCode::SBX,
                        'Start'        => '2019-10-03',
                        'End'          => '2019-10-03',
                        'Rates'        => [
                            'Rate' => [
                                [
                                    'InvTypeCode'     => 'product_id_2',
                                    'CurrencyCode'    => 'EUR',
                                    'BaseByGuestAmts' => [
                                        'BaseByGuestAmt' => [
                                            [
                                                'AmountAfterTax' => 3,
                                                'NumberOfGuests' => 2
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        ];

        $productRateCollection->getPartner()->willReturn($partner);
        $partner->getIdentifier()->willReturn('partner_id');
        $partner->getCurrency()->willReturn('EUR');
        $product1->getIdentifier()->willReturn('product_id_1');
        $product2->getIdentifier()->willReturn('product_id_2');
        $productRate1->getProduct()->willReturn($product1);
        $productRate2->getProduct()->willReturn($product2);
        $productRateCollection->getProductRates()->willReturn([$productRate1, $productRate2]);
        $productRate1->getRates()->willReturn([$rate1]);
        $productRate2->getRates()->willReturn([$rate2, $rate3]);
        $rate1->getStart()->willReturn(new \DateTime('2019-10-01'));
        $rate1->getEnd()->willReturn(new \DateTime('2019-10-01'));
        $rate1->getAmount()->willReturn(1);

        $rate2->getStart()->willReturn(new \DateTime('2019-10-02'));
        $rate2->getEnd()->willReturn(new \DateTime('2019-10-02'));
        $rate2->getAmount()->willReturn(2);

        $rate3->getStart()->willReturn(new \DateTime('2019-10-03'));
        $rate3->getEnd()->willReturn(new \DateTime('2019-10-03'));
        $rate3->getAmount()->willReturn(3);

        $this->normalize($productRateCollection, [])->shouldBeLike($response);
    }
}
