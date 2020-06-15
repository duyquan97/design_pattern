<?php

namespace spec\App\Service\ChannelManager\Wubook\Operation;

use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Exception\ValidationException;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductRateCollection;
use App\Model\ProductRateCollectionInterface;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\Wubook\Operation\GetRatesOperation;
use App\Service\ChannelManager\Wubook\Operation\UpdateDataOperation;
use App\Service\ChannelManager\Wubook\Serializer\WubookSerializer;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;

class UpdateDataOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UpdateDataOperation::class);
    }

    function let(BookingEngineInterface $bookingEngine, WubookSerializer $wubookSerializer, CmhubLogger $logger)
    {
        $this->beConstructedWith($bookingEngine, $wubookSerializer, $logger);
    }

    function it_updates_availability_and_rate(Partner $partner, WubookSerializer $wubookSerializer, ProductRateCollectionInterface $productRateCollection, BookingEngineInterface $bookingEngine, ProductAvailabilityCollection $productAvailabilityCollection, ChannelManager $channelManager)
    {
        $request = [
            "hotel_auth" =>
                [
                    "hotel_id" => "00145577"
                ],
             "data" => [
                 "prices" => ["price data"],
                 "availability" => ["availability data"],
                 "restrictions" => ["restrictions data"]
            ]
        ];

        $partner->getIdentifier()->willReturn('00145577');
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('abc123');
        $wubookSerializer->denormalize(json_decode(json_encode($request))->data, ProductRateCollection::class, ['partner' => $partner])->shouldBeCalled()->willReturn($productRateCollection);
        $wubookSerializer->denormalize(json_decode(json_encode($request))->data, ProductAvailabilityCollection::class, ['partner' => $partner])->shouldBeCalled()->willReturn($productAvailabilityCollection);

        $bookingEngine->updateRates($productRateCollection)->shouldBeCalled();
        $bookingEngine->updateAvailability($productAvailabilityCollection)->shouldBeCalled();

        $this->handle(json_decode(json_encode($request)), $partner)->shouldBeLike([]);
    }

    function it_supports_is_true()
    {
        $this->supports(UpdateDataOperation::NAME)->shouldBe(true);
    }

    function it_supports_is_false()
    {
        $this->supports(GetRatesOperation::NAME)->shouldBe(false);
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
}
