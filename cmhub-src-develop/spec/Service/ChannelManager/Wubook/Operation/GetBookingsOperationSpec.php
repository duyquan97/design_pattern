<?php

namespace spec\App\Service\ChannelManager\Wubook\Operation;

use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Exception\ValidationException;
use App\Model\BookingCollection;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\Wubook\Operation\GetBookingsOperation;
use App\Service\ChannelManager\Wubook\Serializer\BookingCollectionNormalizer;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GetBookingsOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GetBookingsOperation::class);
    }

    function let(BookingEngineInterface $bookingEngine, BookingCollectionNormalizer $bookingCollectionNormalizer, CmhubLogger $logger)
    {
        $this->beConstructedWith($bookingEngine, $bookingCollectionNormalizer, $logger);
    }

    function it_supports(){
        $this->supports('get_bookings')->shouldBe(true);
    }

    function it_throws_exception_if_date_has_not_right_format(Partner $partner)
    {
        $request = [
            "cm_auth" => [
                "username" => "wubook",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_bookings",
            "data" => [
                "start_time" => "2018-0-31 :00:00",
            ],
        ];
        $requestString = json_encode($request);

        $this->shouldThrow(ValidationException::class)->during('handle', [json_decode($requestString), $partner]);
    }

    function it_throws_exception_if_date_is_not_defined(Partner $partner)
    {
        $request = [
            "cm_auth" => [
                "username" => "wubook",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_bookings",
            "data" => [
            ],
        ];
        $requestString = json_encode($request);

        $this->shouldThrow(ValidationException::class)->during('handle', [json_decode($requestString), $partner]);
    }

    function it_gets_bookings(Partner $partner, BookingEngineInterface $bookingEngine, BookingCollection $collection, BookingCollectionNormalizer $bookingCollectionNormalizer, ChannelManager $channelManager)
    {
        $request = [
            "cm_auth" => [
                "username" => "wubook",
                "password" => "password",
            ],
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_bookings",
            "data" => [
                "start_time" => "2018-08-31 15:00:00",
            ],
        ];
        $requestString = json_encode($request);

        $partner->getIdentifier()->willReturn("00145577");
        $partner->getName()->willReturn("Wubook");
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('abc123');
        $bookingEngine->getBookings(
            Argument::that(
                function (\DateTime $from) {
                    return '2018-08-31 15:00:00' === $from->format('Y-m-d H:i:s');
                }
            ), Argument::that(
                function (\DateTime $to) {
                    return date('Y-m-d') === $to->format('Y-m-d');
                }
            ),
            null,
            [$partner])->willReturn($collection);

        $bookingCollectionNormalizer->normalize($collection)->willReturn($response = ['eres' => 'un package']);

        $this->handle(json_decode($requestString), $partner)->shouldBeLike($response);
    }
}
