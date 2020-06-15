<?php

namespace App\Tests\Controller;

use App\Entity\Booking;
use App\Entity\Partner;
use App\Entity\Product;
use App\Tests\BaseWebTestCase;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class IresaControllerTest extends BaseWebTestCase
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
            '/api/int/rest/v1/PushBookings',
            [],
            [],
            [
                'CONTENT_TYPE'       => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ],
            json_encode(
                array(
                    'booking' =>
                        array(
                            'partnerCode'    => '00019371',
                            'status'         => 'Commit',
                            'reservationId'  => '123abc',
                            'createDate'     => '2018-10-05T19:18:07',
                            'lastModifyDate' => '2018-10-05T19:18:07',
                            'dateStart'      => '2017-06-28T19:18:07',
                            'dateEnd'        => '2017-06-30T19:18:07',
                            'totalAmount'    => '325.45',
                            'currency'       => 'EUR',
                            'requests'       => 'requests',
                            'comments'       => 'comments',
                            'roomTypes'      =>
                                array(
                                    0 =>
                                        array(
                                            'roomTypeCode' => '393333',
                                            'totalAmount'  => '325.45',
                                            'currency'     => 'EUR',
                                            'rates'        =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'date'     => '2017-06-28T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'date'     => '2017-06-29T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                ),
                                            'guests'       =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'isMain'      => true,
                                                            'age'         => 18,
                                                            'name'        => 'Mario',
                                                            'surname'     => 'Rossi',
                                                            'email'       => 'mario@rossi.com',
                                                            'phone'       => '0123456789',
                                                            'address'     => 'via Italia',
                                                            'city'        => 'Padova',
                                                            'zip'         => '123',
                                                            'state'       => 'Veneto',
                                                            'country'     => 'Italia',
                                                            'countryCode' => 'IT',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'isMain'  => false,
                                                            'age'     => 18,
                                                            'name'    => '',
                                                            'surname' => ''
                                                        )
                                                ),
                                        ),
                                ),
                            'experienceId'      => '00022222',
                        ),
                )
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful(), $response->getContent());

        /** @var InMemoryTransport $transport */
        $transport = self::$container->get('messenger.transport.booking');
        $this->assertCount(1, $transport->get());

        foreach ($transport->get() as $envelope) {
            $this->assertEquals('123abc', $envelope->getMessage()->getBooking()->getIdentifier());
            $this->assertEquals('confirm', $envelope->getMessage()->getBooking()->getStatus());
        }
    }

    public function testPushBookingsAutoSetUpdateDate()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/PushBookings',
            [],
            [],
            [
                'CONTENT_TYPE'       => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ],
            json_encode(
                array(
                    'booking' =>
                        array(
                            'partnerCode'    => '00019371',
                            'status'         => 'Commit',
                            'reservationId'  => 'RESA-0009564506',
                            'createDate'     => '2018-10-05T19:18:07',
                            'lastModifyDate' => '2018-10-05T19:18:07',
                            'dateStart'      => '2017-06-28T19:18:07',
                            'dateEnd'        => '2017-06-30T19:18:07',
                            'totalAmount'    => '325.45',
                            'currency'       => 'EUR',
                            'requests'       => 'requests',
                            'comments'       => 'comments',
                            'roomTypes'      =>
                                array(
                                    0 =>
                                        array(
                                            'roomTypeCode' => '393333',
                                            'totalAmount'  => '325.45',
                                            'currency'     => 'EUR',
                                            'rates'        =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'date'     => '2017-06-28T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'date'     => '2017-06-29T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                ),
                                            'guests'       =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'isMain'      => 'true',
                                                            'age'         => 18,
                                                            'name'        => 'Mario',
                                                            'surname'     => 'Rossi',
                                                            'email'       => 'mario@rossi.com',
                                                            'phone'       => '0123456789',
                                                            'address'     => 'via Italia',
                                                            'city'        => 'Padova',
                                                            'zip'         => '123',
                                                            'state'       => 'Veneto',
                                                            'country'     => 'Italia',
                                                            'countryCode' => 'IT',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'isMain'  => false,
                                                            'age'     => 18,
                                                            'name'    => 'Mario',
                                                            'surname' => 'Rossi'
                                                        )
                                                ),
                                        ),
                                ),
                            'experienceId'      => '00022222',
                        ),
                )
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful(), $response->getContent());

        /** @var InMemoryTransport $transport */
        $transport = self::$container->get('messenger.transport.booking');
        $this->assertCount(1, $transport->get());

        foreach ($transport->get() as $envelope) {
            $this->assertEquals('RESA-0009564506', $envelope->getMessage()->getBooking()->getIdentifier());
        }
    }

    public function testCancelBookings()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/PushBookings',
            [],
            [],
            [
                'CONTENT_TYPE'       => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ],
            json_encode(
                array(
                    'booking' =>
                        array(
                            'partnerCode'    => '00019371',
                            'status'         => 'Cancel',
                            'reservationId'  => 'RESA-0009564506',
                            'createDate'     => '2018-10-05T19:18:07',
                            'lastModifyDate' => '2018-10-05T19:18:07',
                            'dateStart'      => '2017-06-28T19:18:07',
                            'dateEnd'        => '2017-06-30T19:18:07',
                            'totalAmount'    => '325.45',
                            'currency'       => 'EUR',
                            'requests'       => 'requests',
                            'comments'       => 'comments',
                            'roomTypes'      =>
                                array(
                                    0 =>
                                        array(
                                            'roomTypeCode' => '393333',
                                            'totalAmount'  => '325.45',
                                            'currency'     => 'EUR',
                                            'rates'        =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'date'     => '2017-06-28T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'date'     => '2017-06-29T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                ),
                                            'guests'       =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'isMain'      => 'true',
                                                            'age'         => 18,
                                                            'name'        => 'Mario',
                                                            'surname'     => 'Rossi',
                                                            'email'       => 'mario@rossi.com',
                                                            'phone'       => '0123456789',
                                                            'address'     => 'via Italia',
                                                            'city'        => 'Padova',
                                                            'zip'         => '123',
                                                            'state'       => 'Veneto',
                                                            'country'     => 'Italia',
                                                            'countryCode' => 'IT',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'isMain'  => false,
                                                            'age'     => 18,
                                                            'name'    => 'Mario',
                                                            'surname' => 'Rossi'
                                                        )
                                                ),
                                        ),
                                ),
                            'experienceId'      => '00022222',
                        ),
                )
            )
        );

        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful(), $response->getContent());
    }

    public function testPushBookingsWithNotFoundPartner()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/PushBookings',
            [],
            [],
            [
                'CONTENT_TYPE'       => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ],
            json_encode(
                array(
                    'booking' =>
                        array(
                            'partnerCode'    => '00019371333',
                            'status'         => 'Commit',
                            'reservationId'  => 'abcdefgh',
                            'createDate'     => '2018-10-05T19:18:07',
                            'lastModifyDate' => '2018-10-05T19:18:07',
                            'dateStart'      => '2017-06-28T19:18:07',
                            'dateEnd'        => '2017-06-30T19:18:07',
                            'totalAmount'    => '325.45',
                            'currency'       => 'EUR',
                            'requests'       => 'requests',
                            'comments'       => 'comments',
                            'roomTypes'      =>
                                array(
                                    0 =>
                                        array(
                                            'roomTypeCode' => '393333',
                                            'totalAmount'  => '325.45',
                                            'currency'     => 'EUR',
                                            'rates'        =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'date'     => '2017-06-28T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'date'     => '2017-06-29T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                ),
                                            'guests'       =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'isMain'      => 'true',
                                                            'age'         => 18,
                                                            'name'        => 'Mario',
                                                            'surname'     => 'Rossi',
                                                            'email'       => 'mario@rossi.com',
                                                            'phone'       => '0123456789',
                                                            'address'     => 'via Italia',
                                                            'city'        => 'Padova',
                                                            'zip'         => '123',
                                                            'state'       => 'Veneto',
                                                            'country'     => 'Italia',
                                                            'countryCode' => 'IT',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'isMain'  => false,
                                                            'age'     => 18,
                                                            'name'    => 'Mario',
                                                            'surname' => 'Rossi'
                                                        )
                                                ),
                                        ),
                                ),
                            'experienceId'      => '00022222',
                        ),
                )
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful(), $response->getContent());

        /** @var InMemoryTransport $transport */
        $transport = self::$container->get('messenger.transport.booking');
        $this->assertCount(1, $transport->get());

        foreach ($transport->get() as $envelope) {
            $this->assertEquals('abcdefgh', $envelope->getMessage()->getBooking()->getIdentifier());
            $this->assertEquals('00019371333', $envelope->getMessage()->getBooking()->getPartner());
        }
    }

    public function testPushBookingsWithDisabledPartner()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/PushBookings',
            [],
            [],
            [
                'CONTENT_TYPE'       => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ],
            json_encode(
                array(
                    'booking' =>
                        array(
                            'partnerCode'    => '00019372',
                            'status'         => 'Commit',
                            'reservationId'  => 'abcdefghik',
                            'createDate'     => '2018-10-05T19:18:07',
                            'lastModifyDate' => '2018-10-05T19:18:07',
                            'dateStart'      => '2017-06-28T19:18:07',
                            'dateEnd'        => '2017-06-30T19:18:07',
                            'totalAmount'    => '325.45',
                            'currency'       => 'EUR',
                            'requests'       => 'requests',
                            'comments'       => 'comments',
                            'roomTypes'      =>
                                array(
                                    0 =>
                                        array(
                                            'roomTypeCode' => '286201',
                                            'totalAmount'  => '325.45',
                                            'currency'     => 'EUR',
                                            'rates'        =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'date'     => '2017-06-28T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'date'     => '2017-06-29T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                ),
                                            'guests'       =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'isMain'      => 'true',
                                                            'age'         => 18,
                                                            'name'        => 'Mario',
                                                            'surname'     => 'Rossi',
                                                            'email'       => 'mario@rossi.com',
                                                            'phone'       => '0123456789',
                                                            'address'     => 'via Italia',
                                                            'city'        => 'Padova',
                                                            'zip'         => '123',
                                                            'state'       => 'Veneto',
                                                            'country'     => 'Italia',
                                                            'countryCode' => 'IT',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'isMain'  => false,
                                                            'age'     => 18,
                                                            'name'    => 'Mario',
                                                            'surname' => 'Rossi'
                                                        )
                                                ),
                                        ),
                                ),
                            'experienceId'      => '00011111',
                        ),
                )
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful(), $response->getContent());

        /** @var InMemoryTransport $transport */
        $transport = self::$container->get('messenger.transport.booking');
        $this->assertCount(1, $transport->get());

        foreach ($transport->get() as $envelope) {
            $this->assertEquals('abcdefghik', $envelope->getMessage()->getBooking()->getIdentifier());
        }
    }

    public function testPushBookingsWithNotFoundProduct()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/PushBookings',
            [],
            [],
            [
                'CONTENT_TYPE'       => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic aXJlc2E6Y0FxOXhMS0xOdTd4cXdxcXo5c3RNTmdBa1pNU3FWRzd5YTZGRnJCbg==',
            ],
            json_encode(
                array(
                    'booking' =>
                        array(
                            'partnerCode'    => '00019371',
                            'status'         => 'Commit',
                            'reservationId'  => 'abcdefghiklmn',
                            'createDate'     => '2018-10-05T19:18:07',
                            'lastModifyDate' => '2018-10-05T19:18:07',
                            'dateStart'      => '2017-06-28T19:18:07',
                            'dateEnd'        => '2017-06-30T19:18:07',
                            'totalAmount'    => '325.45',
                            'currency'       => 'EUR',
                            'requests'       => 'requests',
                            'comments'       => 'comments',
                            'roomTypes'      =>
                                array(
                                    0 =>
                                        array(
                                            'roomTypeCode' => '39333344',
                                            'totalAmount'  => '325.45',
                                            'currency'     => 'EUR',
                                            'rates'        =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'date'     => '2017-06-28T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'date'     => '2017-06-29T19:18:07',
                                                            'amount'   => '162.95',
                                                            'currency' => 'EUR',
                                                        ),
                                                ),
                                            'guests'       =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'isMain'      => 'true',
                                                            'age'         => 18,
                                                            'name'        => 'Mario',
                                                            'surname'     => 'Rossi',
                                                            'email'       => 'mario@rossi.com',
                                                            'phone'       => '0123456789',
                                                            'address'     => 'via Italia',
                                                            'city'        => 'Padova',
                                                            'zip'         => '123',
                                                            'state'       => 'Veneto',
                                                            'country'     => 'Italia',
                                                            'countryCode' => 'IT',
                                                        ),
                                                    1 =>
                                                        array(
                                                            'isMain'  => false,
                                                            'age'     => 18,
                                                            'name'    => 'Mario',
                                                            'surname' => 'Rossi'
                                                        )
                                                ),
                                        ),
                                ),
                            'experienceId'      => '00022222',
                        ),
                )
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->isSuccessful(), $response->getContent());

        /** @var InMemoryTransport $transport */
        $transport = self::$container->get('messenger.transport.booking');
        $this->assertCount(1, $transport->get());

        foreach ($transport->get() as $envelope) {
            $this->assertEquals('abcdefghiklmn', $envelope->getMessage()->getBooking()->getIdentifier());
            $this->assertEquals('00019371', $envelope->getMessage()->getBooking()->getPartner());
        }
    }
}
