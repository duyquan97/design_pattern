<?php

namespace spec\App\Service\ChannelManager\Travelclick;

use App\Entity\Booking;
use App\Entity\Partner;
use App\Model\PushBooking;
use App\Service\ChannelManager\Travelclick\TravelclickChannelManager;
use App\Utils\HttpClient;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Twig\Environment;

class TravelclickChannelManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TravelclickChannelManager::class);
    }

    function let(HttpClient $travelclickHttpClient, Environment $templating, CmhubLogger $logger)
    {
        $this->beConstructedWith($travelclickHttpClient, $templating, $logger);
    }

    function it_support_cm_code()
    {
        $this->supports(TravelclickChannelManager::NAME)->shouldBe(true);
    }

    function it_generate_1_room_1_night_request(Environment $templating, PushBooking $pushBooking, Booking $booking)
    {
        $pushBooking->getBooking()->willReturn($booking);
        $templating->render(
            'Api/Ext/Soap/TravelClick/OTA_HotelResNotifRQ.xml.twig',
            Argument::type('array')
        )->willReturn(Argument::type('string'));

        $this->getRequestBody($booking);
    }
}
