<?php

namespace spec\App\Controller;

use App\Controller\AvailProController;
use App\Entity\ChannelManager;
use App\Exception\DateFormatException;
use App\Service\ChannelManager\AvailPro\AvailProIntegration;
use App\Service\ChannelManager\ChannelManagerList;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AvailProControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailProController::class);
    }

    function let(AvailProIntegration $integration)
    {
        $this->beConstructedWith($integration);
    }

    function it_gets_hotel_data(AvailProIntegration $integration, Request $request, Response $response)
    {

        $request->get('hotelCode')->willReturn('id');
        $integration->getHotel('id')->willReturn($data = 'the_data');
        $response->setContent($data)->shouldBeCalled()->willReturn($response);
        $this->getHotelAction($request, $response)->shouldBeAnInstanceOf(Response::class);
    }

    function it_updates_availabilities_and_rates(AvailProIntegration $integration, Request $request, Response $response)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
                <message xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                    <authentication login="availpro" password="availpro" />
                    <inventoryUpdate hotelId="00127978">
                        <room id="722683">
                            <inventory>
                                <availability from="2017-12-28" to="2017-12-30" quantity="10" />
                            </inventory>
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2017-12-28" to="2017-12-28" minimumStay="1" maximumStay="1" unitPrice="210" noArrival="false" noDeparture="false" isClosed="false" />
                            </rate>
                        </room>
                        <room id="722684">
                            <inventory>
                                <availability from="2017-12-28" to="2017-12-28" quantity="1" />
                                <availability from="2017-12-29" to="2017-12-31" quantity="1" />
                            </inventory>
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2017-12-28" to="2017-12-28" minimumStay="1" maximumStay="1" unitPrice="110" noArrival="false" noDeparture="false" isClosed="false" />
                            </rate>
                        </room>
                    </inventoryUpdate>
                </message>';

        $request->getContent()->willReturn($xml);
        $integration->updateAvailabilitiesAndRates(Argument::type('stdClass'))->shouldBeCalled()->willReturn('data');
        $response->setContent('data')->shouldBeCalled()->willReturn($response);
        $this->updateAvailabilitiesAndRatesAction($request, $response)->shouldBeAnInstanceOf(Response::class);
    }

    function it_gets_bookings_between_dates(AvailProIntegration $integration, Request $request, Response $response)
    {
        $request->get('from')->willReturn('2017-10-03T20:00:00');
        $request->get('to')->willReturn('2017-10-13T20:00:00');
        $request->get('hotelId', '')->willReturn('hotel_id');

        $integration
            ->getBookings(
                Argument::that(
                    function (\DateTime $from) {
                        return "2017-10-03 20:00:00" === $from->format('Y-m-d H:i:s');
                    }
                ),
                Argument::that(
                    function (\DateTime $to) {
                        return "2017-10-13 20:00:00" === $to->format('Y-m-d H:i:s');
                    }
                ),
                'hotel_id'
            )
            ->willReturn('the_data');
        $response->setContent('the_data')->shouldBeCalled()->willReturn($response);
        $this->getBookingsAction($request, $response)->shouldBeAnInstanceOf(Response::class);
    }

    function it_gets_bookings_between_dates_with_all_partners(
        AvailProIntegration $integration,
        Request $request,
        Response $response
    ) {
        $request->get('from')->willReturn('2017-10-03T20:00:00');
        $request->get('to')->willReturn('2017-10-13T20:00:00');
        $request->get('hotelId', '')->willReturn('');

        $integration
            ->getBookings(
                Argument::that(
                    function (\DateTime $from) {
                        return "2017-10-03 20:00:00" === $from->format('Y-m-d H:i:s');
                    }
                ),
                Argument::that(
                    function (\DateTime $to) {
                        return "2017-10-13 20:00:00" === $to->format('Y-m-d H:i:s');
                    }
                ),
                ''
            )
            ->willReturn('the_data');
        $response->setContent('the_data')->shouldBeCalled()->willReturn($response);
        $this->getBookingsAction($request, $response)->shouldBeAnInstanceOf(Response::class);
    }

    function it_gets_bookings_by_duration(AvailProIntegration $integration, Request $request, Response $response)
    {
        $request->get('from')->willReturn();
        $request->get('to')->willReturn();
        $request->get('duration')->willReturn('10');
        $request->get('hotelId', '')->willReturn('hotel_id');
        $integration
            ->getBookings(
                Argument::that(function (\DateTime $start) {
                    return (new \DateTime('-10 hour'))->format('Y-m-d H') === $start->format('Y-m-d H');
                }),
                Argument::that(function (\DateTime $end) {
                    return $end->format('Y-m-d H') === date('Y-m-d H');
                }),
                'hotel_id'
            )
            ->willReturn('the_data')
        ;

        $response->setContent('the_data')->shouldBeCalled()->willReturn($response);
        $this->getBookingsAction($request, $response)->shouldBeAnInstanceOf(Response::class);
    }

    function it_gets_bookings_between_dates_by_cm(AvailProIntegration $integration, Request $request, Response $response)
    {
        $request->get('from')->willReturn('2017-10-03T20:00:00');
        $request->get('to')->willReturn('2017-10-13T20:00:00');
        $request->get('hotelId', '')->willReturn('');

        $integration
            ->getBookings(
                Argument::that(
                    function (\DateTime $from) {
                        return "2017-10-03 20:00:00" === $from->format('Y-m-d H:i:s');
                    }
                ),
                Argument::that(
                    function (\DateTime $to) {
                        return "2017-10-13 20:00:00" === $to->format('Y-m-d H:i:s');
                    }
                ),
                ''
            )
            ->willReturn('the_data');
        $response->setContent('the_data')->shouldBeCalled()->willReturn($response);
        $this->getBookingsAction($request, $response)->shouldBeAnInstanceOf(Response::class);
    }

    function it_gets_bookings_by_duration_and_cm(AvailProIntegration $integration, Request $request, Response $response)
    {
        $request->get('from')->willReturn();
        $request->get('to')->willReturn();
        $request->get('duration')->willReturn('10');
        $request->get('hotelId', '')->willReturn('');
        $integration
            ->getBookings(
                Argument::that(function (\DateTime $start) {
                    return (new \DateTime('-10 hour'))->format('Y-m-d H:i:s') === $start->format('Y-m-d H:i:s');
                }),
                Argument::that(function (\DateTime $end) {
                    return $end->format('Y-m-d H:i:s') === date('Y-m-d H:i:s');
                }),
                ''
            )
            ->willReturn('the_data')
        ;

        $response->setContent('the_data')->shouldBeCalled()->willReturn($response);
        $this->getBookingsAction($request, $response)->shouldBeAnInstanceOf(Response::class);
    }

    function it_throws_exception_get_bookings_if_unexpected_format(AvailProIntegration $integration, Request $request, Response $response) {
        $request->get('from')->willReturn('wrong_format');
        $request->get('to')->willReturn('wrong_format');
        $this->shouldThrow(DateFormatException::class)->during('getBookingsAction', [$request, $response]);
    }
}
