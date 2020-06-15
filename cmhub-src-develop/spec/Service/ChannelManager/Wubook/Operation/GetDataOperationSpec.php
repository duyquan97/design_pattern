<?php

namespace spec\App\Service\ChannelManager\Wubook\Operation;

use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Exception\ValidationException;
use App\Model\AvailabilityInterface;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductCollection;
use App\Model\ProductInterface;
use App\Model\ProductRateCollection;
use App\Model\RateInterface;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\Wubook\Operation\GetDataOperation;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GetDataOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GetDataOperation::class);
    }

    function let(ProductLoader $productLoader, BookingEngineInterface $bookingEngine, CmhubLogger $logger)
    {
        $this->beConstructedWith($productLoader, $bookingEngine, $logger);
    }

    function it_throws_exception_data_not_defined(Partner $partner)
    {
        $request = [
            "cm_auth" => [
                "username" => "wubook",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_data",
        ];
        $requestString = json_encode($request);

        $this->shouldThrow(ValidationException::class)->during('handle', [json_decode($requestString), $partner]);
    }

    function it_throws_exception_start_date_not_defined(Partner $partner)
    {
        $request = [
            "cm_auth" => [
                "username" => "wubook",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_data",
            "data" => [
                "end_date" => "2019-12-02",
            ],
        ];
        $requestString = json_encode($request);

        $this->shouldThrow(ValidationException::class)->during('handle', [json_decode($requestString), $partner]);
    }

    function it_throws_exception_end_date_not_defined(Partner $partner)
    {
        $request = [
            "cm_auth" => [
                "username" => "wubook",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_data",
            "data" => [
                "start_date" => "2019-12-02",
            ],
        ];
        $requestString = json_encode($request);

        $this->shouldThrow(ValidationException::class)->during('handle', [json_decode($requestString), $partner]);
    }

    function it_throws_exception_start_date_malformed(Partner $partner)
    {
        $request = [
            "cm_auth" => [
                "username" => "wubook",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_data",
            "data" => [
                "start_date" => "2019-1-01",
                "end_date" => "2019-12-02",
            ],
        ];
        $requestString = json_encode($request);

        $this->shouldThrow(ValidationException::class)->during('handle', [json_decode($requestString), $partner]);
    }

    function it_throws_exception_end_date_malformed(Partner $partner)
    {
        $request = [
            "cm_auth" => [
                "username" => "wubook",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_data",
            "data" => [
                "start_date" => "2019-12-01",
                "end_date" => "2019-1-02",
            ],
        ];
        $requestString = json_encode($request);

        $this->shouldThrow(ValidationException::class)->during('handle', [json_decode($requestString), $partner]);
    }

    function it_throws_exception_start_date_greater_than_end_date(Partner $partner)
    {
        $request = [
            "cm_auth" => [
                "username" => "wubook",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_data",
            "data" => [
                "start_date" => "2019-12-02",
                "end_date" => "2019-12-01",
            ],
        ];
        $requestString = json_encode($request);

        $this->shouldThrow(ValidationException::class)->during('handle', [json_decode($requestString), $partner]);
    }

    function it_supports_operation()
    {
        $this->supports('get_data')->shouldBe(true);
    }

    function it_gets_rate(
        Partner $partner,
        ProductLoader $productLoader,
        ProductInterface $product,
        ProductCollection $productCollection,
        BookingEngineInterface $bookingEngine,
        ProductAvailabilityCollectionInterface $productAvailabilityCollection,
        ProductRateCollection $productRateCollection,
        AvailabilityInterface $availability,
        RateInterface $rate,
        ProductInterface $product1,
        ChannelManager $channelManager
    )
    {
        $request = [
            "cm_auth" => [
                "username" => "wubook",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_data",
            "data" => [
                "start_date" => "2019-12-01",
                "end_date" => "2019-12-02",
            ],
        ];
        $requestString = json_encode($request);

        $partner->getIdentifier()->shouldBeCalled()->willReturn('00145577');
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('abc123');

        $productCollection->addProduct($product)->willReturn($productCollection);
        $productLoader->getByPartner($partner)->shouldBeCalled()->willReturn($productCollection);

        $productCollection->toArray()->shouldBeCalled()->willReturn($arrayProductCollection = [$product, $product1]);

        $bookingEngine->getAvailabilities($partner, Argument::that(
            function (\DateTime $from) {
                return '2019-12-01' === $from->format('Y-m-d');
            }
        ), Argument::that(
            function (\DateTime $from) {
                return '2019-12-02' === $from->format('Y-m-d');
            }
        ), $arrayProductCollection)
            ->shouldBeCalled()->willReturn($productAvailabilityCollection);
        $bookingEngine->getRates($partner, Argument::that(
            function (\DateTime $from) {
                return '2019-12-01' === $from->format('Y-m-d');
            }
        ), Argument::that(
            function (\DateTime $from) {
                return '2019-12-02' === $from->format('Y-m-d');
            }
        ), $arrayProductCollection)
            ->shouldBeCalled()->willReturn($productRateCollection);

        $productCollection->getProducts()->shouldBeCalled()->willReturn([$product, $product1]);
        $product->getIdentifier()->shouldBeCalled()->willReturn('320080');
        $product1->getIdentifier()->shouldBeCalled()->willReturn('366455');
        $productAvailabilityCollection->getByProductAndDate($product, Argument::that(
            function (\DateTime $from) {
                return '2019-12-01' === $from->format('Y-m-d');
            }
        ))
            ->shouldBeCalled()->willReturn($availability);

        $productAvailabilityCollection->getByProductAndDate($product, Argument::that(
            function (\DateTime $from) {
                return '2019-12-02' === $from->format('Y-m-d');
            }
        ))
            ->shouldBeCalled()->willReturn($availability);

        $productRateCollection->getByProductAndDate($product, Argument::that(
            function (\DateTime $from) {
                return '2019-12-01' === $from->format('Y-m-d');
            }
        ))
            ->shouldBeCalled()->willReturn($rate);

        $productRateCollection->getByProductAndDate($product, Argument::that(
            function (\DateTime $from) {
                return '2019-12-02' === $from->format('Y-m-d');
            }
        ))
            ->shouldBeCalled()->willReturn($rate);


        $productAvailabilityCollection->getByProductAndDate($product1, Argument::that(
            function (\DateTime $from) {
                return '2019-12-01' === $from->format('Y-m-d');
            }
        ))
            ->shouldBeCalled()->willReturn($availability);

        $productAvailabilityCollection->getByProductAndDate($product1, Argument::that(
            function (\DateTime $from) {
                return '2019-12-02' === $from->format('Y-m-d');
            }
        ))
            ->shouldBeCalled()->willReturn($availability);

        $productRateCollection->getByProductAndDate($product1, Argument::that(
            function (\DateTime $from) {
                return '2019-12-01' === $from->format('Y-m-d');
            }
        ))
            ->shouldBeCalled()->willReturn($rate);

        $productRateCollection->getByProductAndDate($product1, Argument::that(
            function (\DateTime $from) {
                return '2019-12-02' === $from->format('Y-m-d');
            }
        ))
            ->shouldBeCalled()->willReturn($rate);

        $availability->getStock()->shouldBeCalled()->willReturn(0);
        $rate->getAmount()->shouldBeCalled()->willReturn(0);

        $response = [
            "hotel_id" => "00145577",
            "rooms" => [
                [
                    "room_id" => '320080',
                    "days" => [
                        "2019-12-01" => [
                            "availability" => 0,
                            "rates" => [[
                                "rate_id" => "SBX",
                                "price" => 0,
                            ]],
                        ],
                        "2019-12-02" => [
                            "availability" => 0,
                            "rates" => [[
                                "rate_id" => "SBX",
                                "price" => 0,
                                ]
                            ],
                        ],
                    ],
                ],
                [
                    "room_id" => '366455',
                    "days" => [
                        "2019-12-01" => [
                            "availability" => 0,
                            "rates" => [[
                                "rate_id" => "SBX",
                                "price" => 0,
                            ],
                            ],
                        ],
                        "2019-12-02" => [
                            "availability" => 0,
                            "rates" => [[
                                "rate_id" => "SBX",
                                "price" => 0,
                            ],
                            ],
                        ],
                    ],
                ],
            ],
        ];


        $this->handle(json_decode($requestString), $partner)->shouldBeLike($response);
    }
}
