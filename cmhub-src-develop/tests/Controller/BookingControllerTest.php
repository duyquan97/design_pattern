<?php

namespace App\Tests\Controller;

use App\Entity\Booking;
use App\Tests\BaseWebTestCase;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class BookingControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function testPushBookings()
    {
        $this->client->request(
            'POST',
            '/booking',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                //'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ],
            '{
    "id": "SBX-123829020",
    "status": "confirm",
    "start_date": "2020-01-01",
    "end_date": "2020-01-05",
    "partner_id": "00029382",
    "created_at": "2019-11-22T00:00:00",
    "updated_at": "2019-11-22T00:00:00",
    "currency": "EUR",
    "voucher_number": "12312AS23",
    "price": 367.4,
    "experience": {
        "id":"abc",
        "components": [
            {
                "name": "A great component"
            }
        ],
        "price": 66.6
    },
    "room_types": [
        {
            "id": "839233", 
            "guests": [
                {
                    "is_main": true,
                    "age": 30,
                    "name": "Pepito",
                    "surname": "Los Palotes",
                    "email": "pepito.palote@smartbox.com",
                    "phone": "6786786788",
                    "country_code": "ES" 
                },
                {
                    "is_main": false,
                    "age": 30,
                    "name": "Luz",
                    "surname": "Cuesta Mogollón",
                    "email": "luz.cuesta@smartbox.com",
                    "phone": "6786786788",
                    "country_code": "ES"
                }
            ],
            "daily_rates": [
                {
                    "date": "2020-01-01",
                    "price": 33.3
                },
                {
                    "date": "2020-01-02",
                    "price": 33.3
                },
                {
                    "date": "2020-01-03",
                    "price": 150.40
                },
                {
                    "date": "2020-01-04",
                    "price": 150.40
                }
            ]
        }
    ]
}'
        );

        $response = $this->client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful(), $response->getContent());

        /** @var InMemoryTransport $transport */
        $transport = self::$container->get('messenger.transport.booking');
        $this->assertCount(1, $transport->get());

        foreach ($transport->get() as $envelope) {

            $this->assertEquals('SBX-123829020', $envelope->getMessage()->getBooking()->getIdentifier());
        }
    }

    public function testPushBookingsCheckError()
    {
        $this->client->request(
            'POST',
            '/booking',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                //'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ],
            '{}'
        );

        $responseContent = '{"errors":{"0":"You must specify at least one room_type","id":["This value should not be null.","This value should not be blank."],"created_at":["This value should not be null."],"start_date":["This value should not be null."],"end_date":["This value should not be null."],"status":["This value should not be null.","This value should not be blank."],"price":["This value should not be null.","This value should not be blank."],"currency":["This value should not be null.","This value should not be blank."],"partner_id":["This value should not be null.","This value should not be blank."]}}';
        $response = $this->client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful(), $response->getContent());
        $this->assertContains('errors', $response->getContent());
        $this->assertEquals($responseContent,$response->getContent());

    }

    public function testPushBookingsNoRoomError()
    {
        $this->client->request(
            'POST',
            '/booking',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                //'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ],
            '{
    "id": "SBX-123829020",
    "status": "confirm",
    "start_date": "2020-01-01",
    "end_date": "2020-01-05",
    "partner_id": "00029382",
    "created_at": "2019-11-22T00:00:00",
    "updated_at": "2019-11-22T00:00:00",
    "currency": "EUR",
    "voucher_number": "12312AS23",
    "price": 367.4,
    "experience": {
        "id":"abc",
        "components": [
            {
                "name": "A great component"
            }
        ],
        "price": 66.6
    },
    "room_types": [
        { 
            "guests": [
                {
                    "is_main": true,
                    "age": 30,
                    "name": "Pepito",
                    "surname": "Los Palotes",
                    "email": "pepito.palote@smartbox.com",
                    "phone": "6786786788",
                    "country_code": "ES" 
                },
                {
                    "is_main": false,
                    "age": 30,
                    "name": "Luz",
                    "surname": "Cuesta Mogollón",
                    "email": "luz.cuesta@smartbox.com",
                    "phone": "6786786788",
                    "country_code": "ES"
                }
            ],
            "daily_rates": [
                {
                    "date": "2020-01-01",
                    "price": 33.3
                },
                {
                    "date": "2020-01-02",
                    "price": 33.3
                },
                {
                    "date": "2020-01-03",
                    "price": 150.40
                },
                {
                    "date": "2020-01-04",
                    "price": 150.40
                }
            ]
        }
    ]
}'
        );

        $responseContent = '{"errors":{"room_types":[{"id":["This value should not be null.","This value should not be blank."]}]}}';
        $response = $this->client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful(), $response->getContent());
        $this->assertContains('errors', $response->getContent());
        $this->assertEquals($responseContent, $response->getContent());

    }

    public function testPushBookingsNoGuestOrRateError()
    {
        $this->client->request(
            'POST',
            '/booking',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                //'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ],
            '{
    "id": "SBX-123829020",
    "status": "confirm",
    "start_date": "2020-01-01",
    "end_date": "2020-01-05",
    "partner_id": "00029382",
    "created_at": "2019-11-22T00:00:00",
    "updated_at": "2019-11-22T00:00:00",
    "currency": "EUR",
    "voucher_number": "12312AS23",
    "price": 367.4,
    "experience": {
        "id":"abc",
        "components": [
            {
                "name": "A great component"
            }
        ],
        "price": 66.6
    },
    "room_types": [
        { 
            "id": "839233"
        }
    ]
}'
        );

        $responseContent = '{"errors":["You must specify at least one guest","You must specify at least one daily_rates"]}';
        $response = $this->client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful(), $response->getContent());
        $this->assertContains('errors', $response->getContent());
        $this->assertEquals($responseContent,$response->getContent());

    }


    public function testCancelBookings()
    {
        $this->client->request(
            'POST',
            '/booking/RESA-0009921440/cancel',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                //'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful(), $response->getContent());


    }

    public function testCancelBookingsNotExist()
    {
        $this->client->request(
            'POST',
            '/booking/falce_id/cancel',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                //'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertFalse($response->isSuccessful(), $response->getContent());


    }

}
