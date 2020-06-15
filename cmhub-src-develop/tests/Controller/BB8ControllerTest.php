<?php

namespace App\Tests\Controller;

use App\Entity\Partner;
use App\Entity\Product;
use App\Service\BookingEngineManager;
use App\Tests\BaseWebTestCase;
use DateTime;
use Exception;

class BB8ControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    /**
     * @throws Exception
     */
    public function testInvalidCredentialsOperation()
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/external/availabilities',
                [
                    'startDate'          => '2019-04-30',
                    'endDate'            => '2019-05-01',
                    'externalRoomIds'    => '235854',
                    'externalPartnerIds' => '00019091,00127978'
                ],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Basic wrongbase64string',
                ]
            );

        $response = $this->client->getResponse()->getContent();

        $this->assertContains('Invalid credentials', $response);
        $this->assertFalse($this->client->getResponse()->isSuccessful(), $response);
    }

    /**
     * @throws Exception
     */
    public function testGetAvailabilityOperation()
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/external/availabilities',
                [
                    'startDate'          => '2019-04-30',
                    'endDate'            => '2019-05-01',
                    'externalRoomIds'    => '235854',
                    'externalPartnerIds' => '00019091'
                ],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
                ]
            );

        $response = $this->client->getResponse()->getContent();
        $responseObj = json_decode($response);
        $createdDate = new DateTime($responseObj[0]->externalCreatedAt);
        $updatedDate = new DateTime($responseObj[0]->externalUpdatedAt);
        $dateToday = new DateTime('2019-04-30');
        $dateTomorrow = new DateTime('2019-05-01');
        $expected = '[{"date":"' . $dateToday->format('Y-m-d') . '","quantity":0,"externalRateBandId":"SBX","externalPartnerId":"00019091","externalRoomId":"235854","type":"instant","externalCreatedAt":"' . $createdDate->format('Y-m-d\TH:i:sP') . '","externalUpdatedAt":"' . $updatedDate->format('Y-m-d\TH:i:sP') . '"},{"date":"' . $dateTomorrow->format('Y-m-d') . '","quantity":0,"externalRateBandId":"SBX","externalPartnerId":"00019091","externalRoomId":"235854","type":"instant","externalCreatedAt":"' . $createdDate->format('Y-m-d\TH:i:sP') . '","externalUpdatedAt":"' . $updatedDate->format('Y-m-d\TH:i:sP') . '"}]';

        $this->assertEquals($expected, $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);
    }

    /**
     * @throws Exception
     */
    public function testGetAvailabilityOperationWithoutRoomIds()
    {
        $this->client->request(
            'GET',
            '/api/external/availabilities',
            [
                'startDate'          => '2019-04-30',
                'endDate'            => '2019-05-01',
                'externalPartnerIds' => '00019091,00127978',
            ],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
            ]
        );

        $response = $this->client->getResponse()->getContent();

        $responseObj = json_decode($response);
        $createdDate = new DateTime($responseObj[0]->externalCreatedAt);
        $updatedDate = new DateTime($responseObj[0]->externalUpdatedAt);
        $dateToday = new DateTime('2019-04-30');
        $dateTomorrow = new DateTime('2019-05-01');
        $expected = '[{"date":"' . $dateToday->format('Y-m-d') . '","quantity":0,"externalRateBandId":"SBX","externalPartnerId":"00019091","externalRoomId":"235854","type":"instant","externalCreatedAt":"' . $createdDate->format('Y-m-d\TH:i:sP') . '","externalUpdatedAt":"' . $updatedDate->format('Y-m-d\TH:i:sP') . '"},{"date":"' . $dateTomorrow->format('Y-m-d') . '","quantity":0,"externalRateBandId":"SBX","externalPartnerId":"00019091","externalRoomId":"235854","type":"instant","externalCreatedAt":"' . $createdDate->format('Y-m-d\TH:i:sP') . '","externalUpdatedAt":"' . $updatedDate->format('Y-m-d\TH:i:sP') . '"},';

        $this->assertContains($expected, $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);
    }

    /**
     * @throws Exception
     */
    public function testPostAvailabilityOperation()
    {
        $this->client->request(
            'POST',
            '/api/external/availabilities',
            [],
            [],
            [
                'CONTENT_TYPE'       => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
            ],
            json_encode(
                [
                    [
                        'date'               => '2019-03-20',
                        'quantity'           => 1,
                        'type'               => 'instant',
                        'externalRateBandId' => 'SBX',
                        'externalPartnerId'  => '00019158',
                        'externalRoomId'     => '110224',
                        'externalCreatedAt'  => '2019-03-20T12:22:34.392Z',
                        'externalUpdatedAt'  => '2019-03-20T12:22:34.392Z'
                    ]
                ]
            )
        );

        $response = $this->client->getResponse()->getContent();

        $expected = json_encode(
            []
        );

        $this->assertEquals($expected, $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);
    }

    /**
     * @throws Exception
     */
    public function testGetPricesOperation()
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/external/prices',
                [
                    'startDate'          => '2019-09-30',
                    'endDate'            => '2019-10-01',
                    'externalRoomIds'    => '235854,396872',
                    'externalPartnerIds'  => '00019091,00127978'
                ],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
                ]
            );

        $response = $this->client->getResponse()->getContent();

        $this->assertContains('currencyCode', $response);
        $this->assertContains('date', $response);
        $this->assertContains('externalPartnerId', $response);

        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);
    }

    /**
     * @throws Exception
     */
    public function testGetPricesMissingParameterOperation()
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/external/prices',
                [
                    'startDate'          => '2019-09-30',
                    'endDate'            => '2019-10-01',
                    'externalRoomId'    => '110223',
                ],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
                ]
            );

        $response = $this->client->getResponse()->getContent();
        $responseObj = (array)json_decode($response);
        $this->assertArrayHasKey('error', $responseObj);
        $this->assertFalse($this->client->getResponse()->isSuccessful(), $response);
    }

    /**
     * @throws Exception
     */
    public function testPostPriceOperation()
    {
        $this->client->request(
            'POST',
            '/api/external/prices',
            [],
            [],
            [
                'CONTENT_TYPE'       => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
            ],
            json_encode(
                [
                    [
                        'currencyCode'          => 'EUR',
                        'date'                  => '2019-03-20',
                        'amount'                 => 9990,
                        'rateBandCode'          => 'SBX',
                        'externalPartnerId'     => '00019158',
                        'externalRoomId'        => '110223',
                        'externalCreatedAt'     => '2019-03-20T12:22:34.392Z',
                        'externalUpdatedAt'     => '2019-03-20T12:22:34.392Z'
                    ]
                ]
            )
        );

        $response = $this->client->getResponse()->getContent();

        $expected = json_encode(
            []
        );

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019158']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '110223']);
        $rates = $this->getContainer()->get(BookingEngineManager::class)->getRates($partner, date_create('2019-03-20'), date_create('2019-03-20'), [$product]);
        $this->assertCount(1, $rates);
        foreach ($rates as $productRate) {
            foreach($productRate->getRates() as $rate) {
                $this->assertEquals('2019-03-20', $rate->getStart()->format('Y-m-d'));
                $this->assertEquals(99.90, $rate->getAmount());
            }
        }
        $this->assertEquals($expected, $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);
    }

    /**
     * @throws Exception
     */
    public function testPostPriceOperationAccessDenied()
    {
        $this->client->request(
            'POST',
            '/api/external/prices',
            [],
            [],
            [
                'CONTENT_TYPE'       => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
            ],
            json_encode(
                [
                    [
                        'currencyCode'          => 'EUR',
                        'date'                  => '2019-03-20',
                        'amount'                 => 9990,
                        'rateBandCode'          => 'SBX',
                        'externalPartnerId'     => '00145205',
                        'externalRoomId'        => '463866',
                        'externalCreatedAt'     => '2019-03-20T12:22:34.392Z',
                        'externalUpdatedAt'     => '2019-03-20T12:22:34.392Z'
                    ]
                ]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals('{"error":"Access Denied"}', $response->getContent());
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @throws Exception
     */
    public function testGetRoomsWithPartnerIDOperation()
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/external/rooms',
                [
                    'externalPartnerIds'  => '00019158,00019371',
                ],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
                ]
            );

        $response = $this->client->getResponse()->getContent();
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);

        $response = json_decode($response, true);
        $this->assertCount(4, $response);
        $this->assertEquals('Standard room 504963', $response[0]['title']);
        $this->assertEquals(true, $response[0]['isSellable']);
        $this->assertEquals('00019371', $response[0]['externalPartnerId']);
        $this->assertEquals('504963', $response[0]['externalId']);
        $this->assertEquals('Standard room 504963', $response[0]['description']);
        $this->assertArrayHasKey('externalCreatedAt', $response[0]);
        $this->assertArrayHasKey('externalUpdatedAt', $response[0]);

        $this->assertEquals('Suite room 286201', $response[1]['title']);
        $this->assertEquals(true, $response[1]['isSellable']);
        $this->assertEquals('00019371', $response[1]['externalPartnerId']);
        $this->assertEquals('286201', $response[1]['externalId']);
        $this->assertEquals('Suite room 286201', $response[1]['description']);
        $this->assertArrayHasKey('externalCreatedAt', $response[1]);
        $this->assertArrayHasKey('externalUpdatedAt', $response[1]);

        $this->assertEquals('Suite room 110223', $response[2]['title']);
        $this->assertEquals(true, $response[2]['isSellable']);
        $this->assertEquals('00019158', $response[2]['externalPartnerId']);
        $this->assertEquals('110223', $response[2]['externalId']);
        $this->assertEquals('Suite room 110223', $response[2]['description']);
        $this->assertArrayHasKey('externalCreatedAt', $response[2]);
        $this->assertArrayHasKey('externalUpdatedAt', $response[2]);

        $this->assertEquals('Suite room 110224', $response[3]['title']);
        $this->assertEquals(false, $response[3]['isSellable']);
        $this->assertEquals('00019158', $response[3]['externalPartnerId']);
        $this->assertEquals('110224', $response[3]['externalId']);
        $this->assertEquals('Suite room 110224', $response[3]['description']);
        $this->assertArrayHasKey('externalCreatedAt', $response[3]);
        $this->assertArrayHasKey('externalUpdatedAt', $response[3]);
    }

    /**
     * @throws Exception
     */
    public function testGetRoomsWithPartnerIdAndUpdateFromOperation()
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/external/rooms',
                [
                    'externalPartnerIds'  => '00019158',
                    'externalUpdatedFrom'  => '2019-12-17T06:48:06.123Z'
                ],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
                ]
            );

        $response = $this->client->getResponse()->getContent();
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);

        $response = json_decode($response, true);
        $this->assertCount(1, $response);

        $this->assertEquals('Suite room 110224', $response[0]['title']);
        $this->assertEquals(false, $response[0]['isSellable']);
        $this->assertEquals('00019158', $response[0]['externalPartnerId']);
        $this->assertEquals('110224', $response[0]['externalId']);
        $this->assertEquals('Suite room 110224', $response[0]['description']);
        $this->assertArrayHasKey('externalCreatedAt', $response[0]);
        $this->assertArrayHasKey('externalUpdatedAt', $response[0]);
    }

    /**
     * @throws Exception
     */
    public function testGetRoomsWithUpdateFromOperation()
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/external/rooms',
                [
                    'externalUpdatedFrom'  => '2019-12-17T06:48:06.123Z'
                ],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
                ]
            );

        $response = $this->client->getResponse()->getContent();
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);

        $response = json_decode($response, true);
        $this->assertCount(3, $response);
        $this->assertEquals('Suite room 110224', $response[0]['title']);
        $this->assertEquals(false, $response[0]['isSellable']);
        $this->assertEquals('00019158', $response[0]['externalPartnerId']);
        $this->assertEquals('110224', $response[0]['externalId']);
        $this->assertEquals('Suite room 110224', $response[0]['description']);
        $this->assertArrayHasKey('externalCreatedAt', $response[0]);
        $this->assertArrayHasKey('externalUpdatedAt', $response[0]);

        $this->assertEquals('Suite room 110225', $response[1]['title']);
        $this->assertEquals(true, $response[1]['isSellable']);
        $this->assertEquals('00019160', $response[1]['externalPartnerId']);
        $this->assertEquals('110225', $response[1]['externalId']);
        $this->assertEquals('Suite room 110225', $response[1]['description']);
        $this->assertArrayHasKey('externalCreatedAt', $response[1]);
        $this->assertArrayHasKey('externalUpdatedAt', $response[1]);

        $this->assertEquals('Suite room 110226', $response[2]['title']);
        $this->assertEquals(true, $response[2]['isSellable']);
        $this->assertEquals('00019160', $response[2]['externalPartnerId']);
        $this->assertEquals('110226', $response[2]['externalId']);
        $this->assertEquals('Suite room 110226', $response[2]['description']);
        $this->assertArrayHasKey('externalCreatedAt', $response[2]);
        $this->assertArrayHasKey('externalUpdatedAt', $response[2]);
    }

    /**
     * @throws Exception
     *
     */
    public function testGetBookingsOperation()
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/external/bookings',
                [
                    'startDate'          => '2019-11-01T00:00:00+00:00',
                    'endDate'            => '2019-11-30T00:00:00+00:00',
                    'externalPartnerIds'  => '00019091,00063498',
                    'externalUpdatedFrom' => '2019-11-30T00:00:00+00:00',
                ],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
                ]
            );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('externalPartnerId', $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);
    }

    /**
     * @throws Exception
     */
    public function testGetBookingsMissingParameterOperation()
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/external/bookings',
                [
                    'startDate'          => '2019-09-30T01:20:43+00:00',
                    'endDate'            => '2019-10-01T09:36:43+00:00',
                    'externalPartnerIds'  => '',
                ],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
                ]
            );

        $response = $this->client->getResponse()->getContent();
        $responseObj = (array)json_decode($response);
        $this->assertArrayHasKey('error', $responseObj);
        $this->assertFalse($this->client->getResponse()->isSuccessful(), $response);
    }

    /**
     * @throws Exception
     */
    public function testGetRoomsWithWrongPartnerIDOperation()
    {
        $this
            ->client
            ->request(
                'GET',
                '/api/external/rooms',
                [
                    'externalPartnerIds'  => '0000000,1111111',
                ],
                [],
                [
                    'HTTP_AUTHORIZATION' => 'Basic YmI4OncyNWF0ckpWOXVHdWd1NmU2Ynk2eUpkeDVydnpKWlZKRjl6NW5UQVo=',
                ]
            );

        $response = $this->client->getResponse()->getContent();
        $this->assertFalse($this->client->getResponse()->isSuccessful(), $response);
    }
}
