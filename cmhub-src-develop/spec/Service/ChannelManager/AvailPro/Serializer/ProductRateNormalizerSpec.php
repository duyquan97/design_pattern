<?php

namespace spec\App\Service\ChannelManager\AvailPro\Serializer;

use App\Entity\ChannelManager;
use App\Exception\DateFormatException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\Factory\RateFactory;
use App\Model\PartnerInterface;
use App\Model\ProductInterface;
use App\Model\ProductRateCollection;
use App\Model\Rate;
use App\Service\ChannelManager\AvailPro\AvailProChannelManager;
use App\Service\ChannelManager\AvailPro\Serializer\ProductRateNormalizer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductRateNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRateNormalizer::class);
    }

    function let(
        ProductLoader $productLoader,
        PartnerLoader $partnerLoader,
        RateFactory $rateFactory,
        ProductRateCollectionFactory $productRateCollectionFactory,
        PartnerInterface $partner,
        ProductRateCollection $productRateCollection,
        ChannelManager $channelManager,
        ProductInterface $product,
        ProductInterface $product1
    ) {
        $this->beConstructedWith($productLoader, $partnerLoader, $rateFactory, $productRateCollectionFactory);

        $partnerLoader->find('partner_id')->willReturn($partner);
        $productRateCollectionFactory->create($partner)->willReturn($productRateCollection);
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn(AvailProChannelManager::NAME);
        $partner->getIdentifier()->willReturn('partner_id');
        $productLoader->find($partner, 'roomid1')->willReturn($product);
        $productLoader->find($partner, 'roomid2')->willReturn($product1);
    }

    function it_denormalizes_product_rates(
        RateFactory $rateFactory,
        ProductRateCollection $productRateCollection,
        ProductInterface $product,
        ProductInterface $product1,
        Rate $rate,
        Rate $rate1,
        Rate $rate3
    ) {
        $rateFactory
            ->create(
                Argument::that(
                    function (\DateTime $start) {
                        return '2017-12-28' === $start->format('Y-m-d');
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return '2017-12-30' === $end->format('Y-m-d');
                    }
                ),
                99,
                $product
            )
            ->shouldBeCalled()
            ->willReturn($rate);

        $productRateCollection->addRate($product, $rate)->shouldBeCalled();

        $rateFactory
            ->create(
                Argument::that(
                    function (\DateTime $start) {
                        return '2017-12-10' === $start->format('Y-m-d');
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return '2017-12-20' === $end->format('Y-m-d');
                    }
                ),
                300,
                $product1
            )
            ->shouldBeCalled()
            ->willReturn($rate1);

        $productRateCollection->addRate($product1, $rate1)->shouldBeCalled();

        $rateFactory
            ->create(
                Argument::that(
                    function (\DateTime $start) {
                        return '2018-12-28' === $start->format('Y-m-d');
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return '2018-12-30' === $end->format('Y-m-d');
                    }
                ),
                150,
                $product1
            )
            ->shouldBeCalled()
            ->willReturn($rate3);

        $productRateCollection->addRate($product1, $rate3)->shouldBeCalled();

        $this->denormalize(json_decode(json_encode($this->testData)))->shouldBe($productRateCollection);
    }

    function it_throws_validation_exception_if_wrong_rate_plan()
    {
        $this->testData['inventoryUpdate']['room'][0]['rate']['@attributes']['rateCode'] = 'DIFF_RATE';
        $data = json_decode(json_encode($this->testData));
        $this->shouldThrow(ValidationException::class)->during('denormalize', [$data]);
    }

    function it_throws_validation_exception_if_wrong_date_formats()
    {
        $this->testData['inventoryUpdate']['room'][0]['rate']['planning']['@attributes']['from'] = '12-03-2018';
        $data = json_decode(json_encode($this->testData));
        $this->shouldThrow(DateFormatException::class)->during('denormalize', [$data]);
    }

    function it_throws_exception_if_partner_not_found(
        PartnerLoader $partnerLoader
    ) {
        $partnerLoader->find('partner_id')->willReturn();
        $this->shouldThrow(PartnerNotFoundException::class)->during('denormalize', [json_decode(json_encode($this->testData))]);
    }

    function it_throws_exception_if_partner_is_not_availpro(
        ChannelManager $channelManager
    ) {
        $channelManager->getIdentifier()->willReturn('random');
        $this->shouldThrow(PartnerNotFoundException::class)->during('denormalize', [json_decode(json_encode($this->testData))]);
    }

    function it_does_not_throw_exception_if_product_not_found(
        ChannelManager $channelManager,
        ProductLoader $productLoader,
        PartnerInterface $partner
    ) {
        $channelManager->getIdentifier()->willReturn(AvailProChannelManager::NAME);
        $productLoader->find($partner, 'roomid1')->willReturn();
        $this->shouldThrow(ProductNotFoundException::class)->during('denormalize', [json_decode(json_encode($this->testData))]);
    }

    function it_supports_denormalization_product_rate_collection()
    {
        $this->supportsDenormalization(ProductRateCollection::class)->shouldBe(true);
        $this->supportsDenormalization('different_class')->shouldBe(false);
    }

    function it_does_not_support_normalization()
    {
        $this->supportsNormalization('whatever')->shouldBe(false);
    }

    protected $testData = [
        'inventoryUpdate' => [
            '@attributes' => [
                'hotelId' => 'partner_id'
            ],
            'room'        => [
                [
                    '@attributes' => [
                        'id' => 'roomid1'
                    ],
                    'inventory'   => [
                        'availability' => [
                            [
                                '@attributes' => [
                                    'from'     => '2017-12-28',
                                    'to'       => '2017-12-30',
                                    'quantity' => '10'
                                ]
                            ]
                        ]
                    ],
                    'rate'        => [
                        '@attributes' => [
                            'currency' => 'EUR',
                            'rateCode' => 'SBX',
                            'rateName' => 'SmartBox Standard Rate'
                        ],
                        'planning'    => [
                            '@attributes' => [
                                'from'        => '2017-12-28',
                                'to'          => '2017-12-30',
                                'minimumStay' => '1',
                                'maximumStay' => '1',
                                'unitPrice'   => '99',
                                'noArrival'   => 'false',
                                'noDeparture' => 'false',
                                'isClosed'    => 'false'
                            ]
                        ]
                    ]
                ],
                [
                    '@attributes' => [
                        'id' => 'roomid2'
                    ],
                    'inventory'   => [
                        'availability' => [
                            [
                                '@attributes' => [
                                    'from'     => '2017-12-28',
                                    'to'       => '2017-12-30',
                                    'quantity' => '3'
                                ]
                            ],
                            [
                                '@attributes' => [
                                    'from'     => '2018-12-28',
                                    'to'       => '2018-12-30',
                                    'quantity' => '6'
                                ]
                            ]
                        ]
                    ],
                    'rate'        => [
                        '@attributes' => [
                            'currency' => 'EUR',
                            'rateCode' => 'SBX',
                            'rateName' => 'SmartBox Standard Rate'
                        ],
                        'planning'    => [
                            [
                                '@attributes' => [
                                    'from'        => '2017-12-10',
                                    'to'          => '2017-12-20',
                                    'minimumStay' => '1',
                                    'maximumStay' => '1',
                                    'unitPrice'   => '300',
                                    'noArrival'   => 'false',
                                    'noDeparture' => 'false',
                                    'isClosed'    => 'false'
                                ]
                            ],
                            [
                                '@attributes' => [
                                    'from'        => '2018-12-28',
                                    'to'          => '2018-12-30',
                                    'minimumStay' => '1',
                                    'maximumStay' => '1',
                                    'unitPrice'   => '150',
                                    'noArrival'   => 'false',
                                    'noDeparture' => 'false',
                                    'isClosed'    => 'false'
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        ]
    ];
}
