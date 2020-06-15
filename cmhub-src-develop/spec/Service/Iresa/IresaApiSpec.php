<?php

namespace spec\App\Service\Iresa;

use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductInterface;
use App\Model\ProductRateCollectionInterface;
use App\Model\ProductRateInterface;
use App\Model\RateInterface;
use App\Service\Iresa\IresaApi;
use App\Service\Iresa\IresaClient;
use App\Service\Iresa\Serializer\IresaSerializer;
use PhpSpec\ObjectBehavior;

class IresaApiSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IresaApi::class);
    }

    function let(
        IresaClient $iresaClient,
        IresaSerializer $iresaSerializer
    )
    {
        $this->beConstructedWith(
            $iresaClient,
            $iresaSerializer,
            'fr-FR'
        );
    }

    function it_gets_availability_calling_iresa_client(IresaClient $iresaClient, PartnerInterface $partner, ProductInterface $product, ProductInterface $product1)
    {
        $partner->getIdentifier()->willReturn('partner_id');
        $product->getIdentifier()->willReturn('ROOM-A');
        $product1->getIdentifier()->willReturn('ROOM-B');
        $iresaClient
            ->fetch(
                IresaApi::GET_AVAILABILITIES,
                [
                    'partnerCode'    => 'partner_id',
                    'dateStart'      => '2018-10-22T00:00:00+00:00',
                    'dateEnd'        => '2018-10-26T00:00:00+00:00',
                    'allProductType' => false,
                    'roomTypes'      => [
                        [
                            'roomTypeCode' => 'ROOM-A'
                        ],
                        [
                            'roomTypeCode' => 'ROOM-B'
                        ]
                    ],
                ]
            )
            ->shouldBeCalled()
            ->willReturn($result =
                json_decode(
                    json_encode(
                        [
                            'data' => [
                                'roomTypes' => [
                                    [
                                        'the_result'
                                    ]
                                ]
                            ]
                        ]
                    )
                )
            );

        $start = new \DateTime('2018-10-22', new \DateTimeZone('UTC'));
        $end = new \DateTime('2018-10-25', new \DateTimeZone('UTC'));

        $this
            ->getAvailabilities(
                $partner,
                $start,
                $end,
                [
                    $product,
                    $product1
                ]
            )
            ->shouldBe($result->data->roomTypes);
    }

    function it_updates_availability_calling_iresa_client(IresaClient $iresaClient, IresaSerializer $iresaSerializer, ProductAvailabilityCollectionInterface $availabilityCollection)
    {
        $iresaSerializer->normalize($availabilityCollection)->willReturn($normalizedData = ['normalized' => 'data']);
        $iresaClient
            ->fetch(
                IresaApi::UPDATE_AVAILABILITIES,
                $normalizedData
            )
            ->shouldBeCalled();

        $this->updateAvailabilities($availabilityCollection)->shouldBe($availabilityCollection);
    }

    function it_gets_rates_calling_iresa_client(IresaClient $iresaClient, PartnerInterface $partner, ProductInterface $product, ProductInterface $product1)
    {
        $partner->getIdentifier()->willReturn('partner_id');
        $product->getIdentifier()->willReturn('ROOM-A');
        $product1->getIdentifier()->willReturn('ROOM-B');
        $iresaClient
            ->fetch(
                IresaApi::GET_RATES,
                [
                    'partnerCode'    => 'partner_id',
                    'dateStart'      => '2018-10-22T00:00:00+00:00',
                    'dateEnd'        => '2018-10-26T00:00:00+00:00',
                    // +1 day Iresa
                    'allProductType' => false,
                    'roomTypes'      => [
                        [
                            'roomTypeCode' => 'ROOM-A'
                        ],
                        [
                            'roomTypeCode' => 'ROOM-B'
                        ]
                    ],
                ]
            )
            ->shouldBeCalled()
            ->willReturn($result =
                json_decode(
                    json_encode(
                        [
                            'data' => [
                                'roomTypes' => [
                                    [
                                        'the_result'
                                    ]
                                ]
                            ]
                        ]
                    )
                )
            );

        $start = new \DateTime('2018-10-22', new \DateTimeZone('UTC'));
        $end = new \DateTime('2018-10-25', new \DateTimeZone('UTC'));

        $this
            ->getRates(
                $partner,
                $start,
                $end,
                [
                    $product,
                    $product1
                ]
            )
            ->shouldBe($result->data->roomTypes);
    }

    function it_updates_rates_calling_iresa_client(
        ProductRateCollectionInterface $productRates,
        ProductRateInterface $productRate,
        ProductRateInterface $productRate1,
        ProductInterface $product,
        ProductInterface $product1,
        RateInterface $rate,
        RateInterface $rate1,
        RateInterface $rate2,
        IresaClient $iresaClient,
        PartnerInterface $partner
    )
    {
        $productRate1->getProduct()->willReturn($product1);
        $product1->getIdentifier()->willReturn('product_2');

        $productRate->getRates()->willReturn([
            $rate,
            $rate1
        ]);
        $productRate1->getRates()->willReturn([$rate2]);
        $productRates->getProductRates()->willReturn([
            $productRate,
            $productRate1
        ]);
        $productRates->getPartner()->willReturn($partner);
        $partner->getIdentifier()->willReturn('partner_id');
        $partner->getCurrency()->willReturn('EUR');
        $productRates->setProductRates([
            $productRate,
            $productRate1
        ]);
        $productRate->getProduct()->willReturn($product);
        $product->getIdentifier()->willReturn('product_1');


        $startRate = new \DateTime('2018-09-11');
        $endRate = new \DateTime('2018-09-21');

        $rate->getStart()->willReturn($startRate);
        $rate->getEnd()->willReturn($endRate);
        $rate->getAmount()->willReturn(12);

        $startRate1 = new \DateTime('2018-10-13');
        $endRate1 = new \DateTime('2018-10-21');

        $rate1->getStart()->willReturn($startRate1);
        $rate1->getEnd()->willReturn($endRate1);
        $rate1->getAmount()->willReturn(22);

        $startRate2 = new \DateTime('2018-10-25');
        $endRate2 = new \DateTime('2018-10-29');

        $rate2->getStart()->willReturn($startRate2);
        $rate2->getEnd()->willReturn($endRate2);
        $rate2->getAmount()->willReturn(92);

        $iresaClient
            ->fetch(
                IresaApi::UPDATE_RATES,
                [
                    'partnerCode' => 'partner_id',
                    'rates'       => [
                        [
                            'roomTypeCode' => 'product_1',
                            'currency'     => 'EUR',
                            'roomTypes'        => [
                                [
                                    'dateStart' => '2018-09-11',
                                    'dateEnd'   => '2018-09-22',
                                    'amount'    => 12,
                                ],
                                [
                                    'dateStart' => '2018-10-13',
                                    'dateEnd'   => '2018-10-22',
                                    'amount'    => 22,
                                ],

                            ],
                        ],
                        [
                            'roomTypeCode' => 'product_2',
                            'currency'     => 'EUR',
                            'roomTypes'        => [
                                [
                                    'dateStart' => '2018-10-25',
                                    'dateEnd'   => '2018-10-30',
                                    'amount'    => 92,
                                ],
                            ],
                        ],
                    ]
                ]
            )
            ->shouldBeCalled();

        $this->updateRates($productRates)->shouldBe($productRates);
    }

    function it_gets_bookings_calling_iresa_client(
        IresaClient $iresaClient,
        PartnerInterface $partner
    )
    {
        $partner->getIdentifier()->willReturn('partner_id');
        $start = new \DateTime('2018-10-20');
        $end = new \DateTime('2018-10-25');
        $iresaClient
            ->fetch(
                IresaApi::GET_BOOKINGS,
                [
                    'partnerCode'           => 'partner_id',
                    'dateStartLastModified' => '2018-10-20',
                    'dateEndLastModified'   => '2018-10-26',
                    'allProductType'        => false,
                ]
            )
            ->willReturn(
                $result = json_decode(json_encode(
                    [
                        'data' => [
                            'bookings' => [
                                [
                                    'the_bookings'
                                ]
                            ]
                        ]
                    ]
                ))
            );

        $this->getBookings($partner, $start, $end)->shouldBe($result->data->bookings);
    }

    function it_gets_products_calling_iresa_client(
        IresaClient $iresaClient,
        PartnerInterface $partner
    )
    {
        $partner->getIdentifier()->willReturn('partner_id');
        $iresaClient
            ->fetch(
                IresaApi::GET_PRODUCTS,
                [
                    'partnerCode'           => 'partner_id',
                    'langCode'           => 'fr-FR',
                ]
            )
            ->willReturn(
                $result = json_decode(json_encode(
                    [
                        'data' => [
                            'listRoom' => [
                                [
                                    'the_products'
                                ]
                            ]
                        ]
                    ]
                ))
            );

        $this->getProducts($partner)->shouldBe($result->data->listRoom);
    }
}
