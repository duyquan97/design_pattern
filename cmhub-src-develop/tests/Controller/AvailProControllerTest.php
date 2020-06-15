<?php

namespace App\Tests\Controller;

use App\Entity\Availability;
use App\Entity\Partner;
use App\Entity\Product;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AvailProControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function testGetHotelForbidden()
    {
        $this->client->request(
            'GET',
            '/api/ext/xml/availpro/v1/GetHotel?login=xxxxx&password=yyyyy&hotelCode=00127978',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
    }

    public function testGetHotel()
    {
        $this->client->request(
            'GET',
            '/api/ext/xml/availpro/v1/GetHotel?login=availpro&password=password&hotelCode=00127978',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            ''
        );

        $response = $this->client->getResponse();

        $this->assertContains('<rooms hotelId="00127978" hotelName="Availpro Partner 1">', $response->getContent());
        $this->assertContains('<room code="409904" name="Standard room 409904"><rate code="SBX" name="Smartbox Standard Rate" regime="Standard" /></room>', $response->getContent());
        $this->assertContains('<room code="396872" name="Suite room 396872"><rate code="SBX" name="Smartbox Standard Rate" regime="Standard" /></room>', $response->getContent());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
    }

    public function testGetBookingTimeSpanForbidden()
    {
        $this->client->request(
            'GET',
            '/api/ext/xml/availpro/v1/GetBookings?login=xxxxx&password=yyyyy&from=2017-10-03T20:00:00&to=2017-10-06T21:00:00&hotelId=00127978',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
    }

    public function testGetBookingsTimeSpan()
    {
        $start = (new \DateTime('-3 day'))->format('Y-m-d\TH:i:s');
        $end = (new \DateTime())->format('Y-m-d\TH:i:s');
        $this->client->request(
            'GET',
            '/api/ext/xml/availpro/v1/GetBookings?login=availpro&password=password&from=' . $start . '&to=' . $end . '&hotelId=00127978',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            ''
        );

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
    }

    public function testGetBookingsTimeSpanAllPartners()
    {
        $start = (new \DateTime('-3 day'))->format('Y-m-d\TH:i:s');
        $end = (new \DateTime())->format('Y-m-d\TH:i:s');
        $this->client->request(
            'GET',
            '/api/ext/xml/availpro/v1/GetBookings?login=availpro&password=password&from=' . $start . '&to=' . $end,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            ''
        );

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
    }

    public function testGetBookingsDurationAllPartners()
    {
        $this->client->request(
            'GET',
            '/api/ext/xml/availpro/v1/GetBookings?login=availpro&password=password&duration=5',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            ''
        );

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
    }

    public function testGetBookingDurationForbidden()
    {
        $this->client->request(
            'GET',
            '/api/ext/xml/availpro/v1/' .
            'GetBookings' .
            '?login=xxxxx&password=yyyyy&duration=5&hotelId=00127978',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
    }

    public function testUpdateAvailabilitiesAndRatesForbidden()
    {
        $this->client->request(
            'POST',
            '/api/ext/xml/availpro/v1/UpdateAvailabilitiesAndRates',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            $this->getUpdateAvailAndRatesRequest('wrong', 'wrong')
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
    }

    public function testUpdateAvailabilitiesAndRates()
    {
        $this->client->request(
            'POST',
            '/api/ext/xml/availpro/v1/UpdateAvailabilitiesAndRates',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            $this->getUpdateAvailAndRatesRequest('availpro', 'password')
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
    }

    public function testUpdateAvailabilitiesAndRatesWithoutInventory()
    {
        $this->client->request(
            'POST',
            '/api/ext/xml/availpro/v1/UpdateAvailabilitiesAndRates',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            $this->getUpdateRatesRequest('availpro', 'password')
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));
    }

    public function testStopSaleRequest()
    {
        $this->client->request(
            'POST',
            '/api/ext/xml/availpro/v1/UpdateAvailabilitiesAndRates',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            $this->getStopSaleRequest('availpro', 'password')
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00127978']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '409904']);
        $availabilities = $this->getRepository(Availability::class)->findByDateRange(
            $partner,
            new \DateTime('2019-05-01'),
            new \DateTime('2019-05-03'),
            [$product]
        );

        /** @var Availability $availability */
        foreach ($availabilities as $availability) {
            $this->assertEquals(true, $availability->isStopSale());
        }
    }

    public function testDuplicatedDateRequest()
    {
        $this->client->request(
            'POST',
            '/api/ext/xml/availpro/v1/UpdateAvailabilitiesAndRates',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/xml',
            ],
            $this->getDuplicatedDateRequest('availpro', 'password')
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertContains('text/xml', $response->headers->get('Content-Type'));

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00127978']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '409904']);
        $availabilities = $this->getRepository(Availability::class)->findByDateRange(
            $partner,
            new \DateTime('2019-04-28'),
            new \DateTime('2019-05-10'),
            [$product]
        );

        $this->assertEquals(13, count($availabilities));
        /** @var Availability $availability */
        foreach ($availabilities as $availability) {
            $this->assertEquals($availability->getStart()->format('Y-m-d'), $availability->getEnd()->format('Y-m-d'));
            if (
                $availability->getStart() < new \DateTime('2019-05-01') ||
                $availability->getStart() > new \DateTime('2019-05-03')
            ) {
                $this->assertEquals(1, $availability->getStock());
            } else {
                $this->assertEquals(5, $availability->getStock());
            }
        }
    }

    public function getUpdateAvailAndRatesRequest($username, $password)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <message xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                    <authentication login="' . $username . '" password="' . $password . '" />
                    <inventoryUpdate hotelId="00127978">
                        <room id="409904">
                            <inventory>
                                <availability from="2017-12-28" to="2017-12-28" quantity="1" />
                                <availability from="2017-12-29" to="2017-12-31" quantity="2" />
                                <availability from="2018-01-01" to="2018-01-01" quantity="3" />
                                <availability from="2018-01-02" to="2018-03-31" quantity="4" />
                                <availability from="2018-04-01" to="2018-12-30" quantity="5" />
                            </inventory>
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2017-12-28" to="2017-12-28" minimumStay="1" maximumStay="1" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2017-12-29" to="2017-12-31" minimumStay="1" maximumStay="1" unitPrice="273" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-01-01" to="2018-01-01" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-01-02" to="2018-03-31" minimumStay="1" maximumStay="1" unitPrice="294" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-04-01" to="2018-12-30" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="false" />
                            </rate>
                        </room>
                        <room id="396872">
                            <inventory>
                                <availability from="2017-12-28" to="2017-12-28" quantity="1" />
                                <availability from="2017-12-29" to="2017-12-31" quantity="1" />
                                <availability from="2018-01-01" to="2018-01-01" quantity="1" />
                                <availability from="2018-01-02" to="2018-03-31" quantity="1" />
                                <availability from="2018-04-01" to="2018-12-30" quantity="1" />
                            </inventory>
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2017-12-28" to="2017-12-28" minimumStay="1" maximumStay="1" unitPrice="210" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2017-12-29" to="2017-12-31" minimumStay="1" maximumStay="1" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-01-01" to="2018-01-01" minimumStay="1" maximumStay="1" unitPrice="" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-01-02" to="2018-03-31" minimumStay="1" maximumStay="1" unitPrice="294" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-04-01" to="2018-12-30" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="false" />
                            </rate>
                        </room>
                    </inventoryUpdate>
                </message>';
    }

    public function getUpdateRatesRequest($username, $password)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <message xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                    <authentication login="' . $username . '" password="' . $password . '" />
                    <inventoryUpdate hotelId="00127978">
                        <room id="409904">
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2017-12-28" to="2017-12-28" minimumStay="1" maximumStay="1" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2017-12-29" to="2017-12-31" minimumStay="1" maximumStay="1" unitPrice="273" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-01-01" to="2018-01-01" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-01-02" to="2018-03-31" minimumStay="1" maximumStay="1" unitPrice="294" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-04-01" to="2018-12-30" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="false" />
                            </rate>
                        </room>
                        <room id="396872">
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2017-12-28" to="2017-12-28" minimumStay="1" maximumStay="1" unitPrice="210" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2017-12-29" to="2017-12-31" minimumStay="1" maximumStay="1" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-01-01" to="2018-01-01" minimumStay="1" maximumStay="1" unitPrice="" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-01-02" to="2018-03-31" minimumStay="1" maximumStay="1" unitPrice="294" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-04-01" to="2018-12-30" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="false" />
                            </rate>
                        </room>
                    </inventoryUpdate>
                </message>';
    }

    public function getStopSaleRequest($username, $password)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <message xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                    <authentication login="' . $username . '" password="' . $password . '" />
                    <inventoryUpdate hotelId="00127978">
                        <room id="409904">
                            <inventory>
                                <availability from="2018-12-28" to="2018-12-28" quantity="1" />
                                <availability from="2019-05-01" to="2019-05-03" quantity="5" />
                            </inventory>
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2019-05-01" to="2019-05-03" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="true" />
                            </rate>
                        </room>
                        <room id="396872">
                            <inventory>
                                <availability from="2018-01-01" to="2018-01-01" quantity="1" />
                                <availability from="2018-01-02" to="2018-03-31" quantity="1" />
                                <availability from="2018-04-01" to="2018-12-30" quantity="1" />
                            </inventory>
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2018-01-01" to="2018-01-01" minimumStay="1" maximumStay="1" unitPrice="" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-01-02" to="2018-03-31" minimumStay="1" maximumStay="1" unitPrice="294" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-04-01" to="2018-12-30" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="false" />
                            </rate>
                        </room>
                    </inventoryUpdate>
                </message>';
    }

    public function getDuplicatedDateRequest($username, $password)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <message xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                    <authentication login="' . $username . '" password="' . $password . '" />
                    <inventoryUpdate hotelId="00127978">
                        <room id="409904">
                            <inventory>
                                <availability from="2019-04-28" to="2019-05-10" quantity="1" />
                                <availability from="2019-05-01" to="2019-05-03" quantity="5" />
                            </inventory>
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2019-05-01" to="2019-05-03" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="false" />
                            </rate>
                        </room>
                    </inventoryUpdate>
                </message>';
    }
}
