<?php

namespace spec\App\Service\ChannelManager\AvailPro\Serializer;

use App\Entity\ChannelManager;
use App\Entity\Product;
use App\Exception\DateFormatException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Availability;
use App\Model\Factory\AvailabilityFactory;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductInterface;
use App\Service\ChannelManager\AvailPro\AvailProChannelManager;
use App\Service\ChannelManager\AvailPro\Serializer\ProductAvailabilityNormalizer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAvailabilityNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAvailabilityNormalizer::class);
    }

    function let(
        PartnerLoader $partnerLoader,
        ProductLoader $productLoader,
        ProductAvailabilityCollectionFactory $availabilityCollectionFactory,
        ProductAvailabilityCollection $availabilityCollection,
        AvailabilityFactory $availabilityFactory,
        Availability $availability,
        Availability $availability1,
        PartnerInterface $partner,
        ChannelManager $channelManager,
        ProductInterface $product,
        ProductInterface $product1,
        CmhubLogger $logger
    )
    {
        $this->beConstructedWith($partnerLoader, $productLoader, $availabilityCollectionFactory, $availabilityFactory,$logger);

        $partnerLoader->find('123123')->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn(AvailProChannelManager::NAME);
        $productLoader->find($partner, 'roomid1')->willReturn($product);
        $productLoader->find($partner, 'roomid2')->willReturn($product1);
        $partner->getIdentifier()->willReturn('partner_id');
        $availabilityCollectionFactory->create($partner)->willReturn($availabilityCollection);
    }

    function it_denormalizes_availability_data(
        AvailabilityFactory $availabilityFactory,
        Availability $availability,
        Availability $availability1,
        Availability $availability2,
        Product $product,
        Product $product1,
        ProductAvailabilityCollection $availabilityCollection
    )
    {
        $availability->setStopSale(false)->shouldBeCalled();
        $availability1->setStopSale(false)->shouldBeCalled();
        $availability2->setStopSale(false)->shouldBeCalled();
        $availabilityFactory
            ->create(
                Argument::that(
                    function (\DateTime $start) {
                        return $start->format('Y-m-d') === '2017-12-28';
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return $end->format('Y-m-d') === '2017-12-30';
                    }
                ),
                10,
                $product
            )
            ->willReturn($availability);

        $availabilityFactory
            ->create(
                Argument::that(
                    function (\DateTime $start) {
                        return $start->format('Y-m-d') === '2017-12-28';
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return $end->format('Y-m-d') === '2017-12-30';
                    }
                ),
                1,
                $product1
            )
            ->willReturn($availability1);

        $availabilityFactory
            ->create(
                Argument::that(
                    function (\DateTime $start) {
                        return $start->format('Y-m-d') === '2018-12-28';
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return $end->format('Y-m-d') === '2018-12-30';
                    }
                ),
                1,
                $product1
            )
            ->willReturn($availability2);

        $availabilityCollection->addAvailability($availability)->shouldBeCalled();
        $availabilityCollection->addAvailability($availability1)->shouldBeCalled();
        $availabilityCollection->addAvailability($availability2)->shouldBeCalled();

        $this->denormalize(json_decode(json_encode($this->testData)))->shouldBe($availabilityCollection);
    }

    function it_denormalizes_stop_sale_availability_data(
        AvailabilityFactory $availabilityFactory,
        Availability $availability,
        Availability $availability1,
        Availability $availability2,
        Product $product,
        Product $product1,
        ProductAvailabilityCollection $availabilityCollection
    )
    {
        $availability->setStopSale(true)->shouldBeCalled();
        $availability1->setStopSale(false)->shouldBeCalled();
        $availability2->setStopSale(false)->shouldBeCalled();
        $availabilityFactory
            ->create(
                Argument::that(
                    function (\DateTime $start) {
                        return $start->format('Y-m-d') === '2018-12-28';
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return $end->format('Y-m-d') === '2018-12-30';
                    }
                ),
                1,
                $product
            )
            ->willReturn($availability);

        $availabilityFactory
            ->create(
                Argument::that(
                    function (\DateTime $start) {
                        return $start->format('Y-m-d') === '2018-01-02';
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return $end->format('Y-m-d') === '2018-03-31';
                    }
                ),
                10,
                $product1
            )
            ->willReturn($availability1);

        $availabilityFactory
            ->create(
                Argument::that(
                    function (\DateTime $start) {
                        return $start->format('Y-m-d') === '2018-04-01';
                    }
                ),
                Argument::that(
                    function (\DateTime $end) {
                        return $end->format('Y-m-d') === '2018-12-30';
                    }
                ),
                10,
                $product1
            )
            ->willReturn($availability2);

        $availabilityCollection->addAvailability($availability)->shouldBeCalled();
        $availabilityCollection->addAvailability($availability1)->shouldBeCalled();
        $availabilityCollection->addAvailability($availability2)->shouldBeCalled();

        $this->denormalize($this->getRequest('availpro', 'password'))->shouldBe($availabilityCollection);
    }

    function it_does_not_throw_exception_if_product_not_found(PartnerInterface $partner, ProductLoader $productLoader,CmhubLogger $logger)
    {
        $productLoader->find($partner, 'roomid1')->willReturn();
        $productLoader->find($partner, 'roomid2')->willReturn();
        $logger->addOperationException(Argument::cetera())->shouldBeCalledTimes(2);


        $this->denormalize($this->getRequest('availpro', 'password'));
    }

    function it_throws_date_format_exception_if_wrong_date_format()
    {
        $data = json_decode(json_encode($this->testData), false);
        $data->inventoryUpdate->room[0]->inventory->availability->{'@attributes'}->from = '28-12-2017';
        $this->shouldThrow(DateFormatException::class)->during('denormalize', [$data]);
    }

    function it_throws_exception_if_partner_not_found(PartnerLoader $partnerLoader)
    {
        $partnerLoader->find('123123')->willReturn();
        $this->shouldThrow(PartnerNotFoundException::class)->during('denormalize', [json_decode(json_encode($this->testData))]);
    }

    function it_throws_exception_if_quantity_less_than_zero()
    {
        $data = json_decode(json_encode($this->testData), false);
        $data->inventoryUpdate->room[0]->inventory->availability->{'@attributes'}->quantity = -1;
        $this->shouldThrow(ValidationException::class)->during('denormalize', [$data]);
    }

    function it_throws_exception_if_quantity_greater_than_99999()
    {
        $data = json_decode(json_encode($this->testData), false);
        $data->inventoryUpdate->room[0]->inventory->availability->{'@attributes'}->quantity = 999991;
        $this->shouldThrow(ValidationException::class)->during('denormalize', [$data]);
    }

    protected $testData = [
        'authentication'  => [
            '@attributes' => [
                'login'    => 'username',
                'password' => 'password'
            ]
        ],
        'inventoryUpdate' => [
            '@attributes' => [
                'hotelId' => '123123'
            ],
            'room'        => [
                [
                    '@attributes' => [
                        'id' => 'roomid1'
                    ],
                    'inventory'   => [
                        'availability' => [
                            '@attributes' => [
                                'from'     => '2017-12-28',
                                'to'       => '2017-12-30',
                                'quantity' => '10'
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
                                    'quantity' => '1'
                                ]
                            ],
                            [
                                '@attributes' => [
                                    'from'     => '2018-12-28',
                                    'to'       => '2018-12-30',
                                    'quantity' => '1'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    function getRequest($username, $password)
    {
        $request = '<?xml version="1.0" encoding="utf-8"?>
                <message xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                    <authentication login="' . $username . '" password="' . $password . '" />
                    <inventoryUpdate hotelId="123123">
                        <room id="roomid1">
                            <inventory>
                                <availability from="2018-12-28" to="2018-12-30" quantity="1" />
                            </inventory>
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2018-12-28" to="2018-12-30" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="true" />
                            </rate>
                        </room>
                        <room id="roomid2">
                            <inventory>
                                <availability from="2018-01-02" to="2018-03-31" quantity="10" />
                                <availability from="2018-04-01" to="2018-12-30" quantity="10" />
                            </inventory>
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2018-01-02" to="2018-03-31" minimumStay="1" maximumStay="1" unitPrice="294" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-04-01" to="2018-12-30" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="false" />
                            </rate>
                        </room>
                    </inventoryUpdate>
                </message>';

        $xml = simplexml_load_string($request, "SimpleXMLElement", LIBXML_NOCDATA);
        return json_decode(json_encode($xml));
    }
}
