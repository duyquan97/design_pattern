<?php

namespace App\Tests\Controller;

use App\Entity\Availability;
use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\ProductRate;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WubookControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function testGetRatesOperation()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "get_rates",
                    "data"       =>
                        array(
                            "data1" => "dataprovided1",
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":200,"data":{"hotel_id":"00145577","rates":[{"rate_id":"SBX","name":"Smartbox Standard Rate","currency":"EUR","rooms":["320080","366455"]}]}}', $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);
    }

    public function testGetRoomsOperation()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "get_rooms",
                    "data"       =>
                        array(
                            "data1" => "dataprovided1",
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":200,"data":{"hotel_id":"00145577","rooms":[{"room_id":"320080","name":"Standard room 320080"},{"room_id":"366455","name":"Suite room 366455"}]}}', $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);
    }

    public function testUnauthorizedOperation()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00019091",
                        ),
                    "action"     => "get_rates",
                    "data"       =>
                        array(
                            "data1" => "dataprovided1",
                        ),
                )
            )
        );

        $this->assertEquals(402, $this->client->getResponse()->getStatusCode());
    }

    public function testUnauthenticatedOperation()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "availpro",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "get_rates",
                    "data"       =>
                        array(
                            "data1" => "dataprovided1",
                        ),
                )
            )
        );

        $this->assertEquals(402, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperation()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-08",
                                    "dto"     => "2019-09-18",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-19",
                                    "dto"     => "2019-09-22",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-23",
                                    "dto"     => "2019-09-27",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                        ),
                )
            )
        );


        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":200}', $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00145577']);
        $product1 = $this->getRepository(Product::class)->findOneBy(['identifier' => '320080']);
        $product2 = $this->getRepository(Product::class)->findOneBy(['identifier' => '366455']);
        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-09-01'), new \DateTime('2019-09-07'), [$product1]);
        foreach ($availabilities as $availability) {
            $this->assertEquals(0, $availability->getStock());
        }

        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-09-08'), new \DateTime('2019-09-18'), [$product2]);
        foreach ($availabilities as $availability) {
            $this->assertEquals(0, $availability->getStock());
        }

        $prices = $this->getRepository(ProductRate::class)
            ->findByDateRange($partner, new \DateTime('2019-09-19'), new \DateTime('2019-09-22'), [$product2]);
        foreach ($prices as $price) {
            $this->assertEquals(106.45, $price->getAmount());
        }

        $prices = $this->getRepository(ProductRate::class)
            ->findByDateRange($partner, new \DateTime('2019-09-23'), new \DateTime('2019-09-27'), [$product1]);
        foreach ($prices as $price) {
            $this->assertEquals(106.45, $price->getAmount());
        }
    }

    public function testUpdateAvailabilityWithRestrictionOnExisting()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 2
                                ),
                                array(
                                    "dfrom"   => "2019-09-08",
                                    "dto"     => "2019-09-18",
                                    "room_id" => "366455",
                                    "avail"   => 4
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-23",
                                    "dto"     => "2019-09-27",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-19",
                                    "dto"     => "2019-09-22",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                            "restrictions" => array(
                                array(
                                    "minstay" => 1,
                                    "room_id" => "320080",
                                    "closed" => true,
                                    "rate_id" => "SBX",
                                    "dfrom" => "2019-09-02",
                                    "dto" => "2019-09-06"
                                ),
                                array(
                                    "minstay" => 1,
                                    "room_id" => "366455",
                                    "closed" => true,
                                    "rate_id" => "SBX",
                                    "dfrom" => "2019-09-12",
                                    "dto" => "2019-09-18"
                                )
                            ),
                        ),
                )
            )
        );


        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":200}', $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00145577']);
        $product1 = $this->getRepository(Product::class)->findOneBy(['identifier' => '320080']);
        $product2 = $this->getRepository(Product::class)->findOneBy(['identifier' => '366455']);

        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-09-01'), new \DateTime('2019-09-01'), [$product1]);
        foreach ($availabilities as $availability) {
            $this->assertFalse($availability->isStopSale());
        }

        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-09-02'), new \DateTime('2019-09-06'), [$product1]);
        foreach ($availabilities as $availability) {
            $this->assertTrue($availability->isStopSale());
        }

        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-09-07'), new \DateTime('2019-09-07'), [$product1]);
        foreach ($availabilities as $availability) {
            $this->assertFalse($availability->isStopSale());
        }

        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-09-08'), new \DateTime('2019-09-11'), [$product1]);
        foreach ($availabilities as $availability) {
            $this->assertFalse($availability->isStopSale());
        }

        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-09-12'), new \DateTime('2019-09-18'), [$product1]);
        foreach ($availabilities as $availability) {
            $this->assertTrue($availability->isStopSale());
        }
    }

    public function testUpdateAvailabilityWithRestrictionOnNewDay()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-08",
                                    "dto"     => "2019-09-12",
                                    "room_id" => "320080",
                                    "avail"   => 2
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-23",
                                    "dto"     => "2019-09-27",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                            "restrictions" => array(
                                array(
                                    "minstay" => 1,
                                    "room_id" => "320080",
                                    "closed" => true,
                                    "rate_id" => "SBX",
                                    "dfrom" => "2019-09-14",
                                    "dto" => "2019-09-17"
                                ),
                            ),
                        ),
                )
            )
        );


        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":200}', $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00145577']);
        $product1 = $this->getRepository(Product::class)->findOneBy(['identifier' => '320080']);

        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-09-08'), new \DateTime('2019-09-12'), [$product1]);
        $this->assertCount(5, $availabilities);
        foreach ($availabilities as $availability) {
            $this->assertFalse($availability->isStopSale());
        }

        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-09-14'), new \DateTime('2019-09-17'), [$product1]);
        $this->assertCount(4, $availabilities);
        foreach ($availabilities as $availability) {
            $this->assertTrue($availability->isStopSale());
        }
    }

    public function testUpdateAvailabilityWithRestrictionOverlap()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-18",
                                    "dto"     => "2019-09-25",
                                    "room_id" => "320080",
                                    "avail"   => 2
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-23",
                                    "dto"     => "2019-09-27",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                            "restrictions" => array(
                                array(
                                    "minstay" => 1,
                                    "room_id" => "320080",
                                    "closed" => true,
                                    "rate_id" => "SBX",
                                    "dfrom" => "2019-09-22",
                                    "dto" => "2019-09-27"
                                ),
                            ),
                        ),
                )
            )
        );


        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":200}', $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00145577']);
        $product1 = $this->getRepository(Product::class)->findOneBy(['identifier' => '320080']);

        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-09-18'), new \DateTime('2019-09-21'), [$product1]);
        $this->assertCount(4, $availabilities);
        foreach ($availabilities as $availability) {
            $this->assertFalse($availability->isStopSale());
        }

        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-09-22'), new \DateTime('2019-09-27'), [$product1]);
        $this->assertCount(6, $availabilities);
        foreach ($availabilities as $availability) {
            $this->assertTrue($availability->isStopSale());
        }
    }

    public function testUpdateAvailabilityWithOnlyRestriction()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "restrictions" => array(
                                array(
                                    "minstay" => 1,
                                    "room_id" => "320080",
                                    "closed" => true,
                                    "rate_id" => "SBX",
                                    "dfrom" => "2019-03-14",
                                    "dto" => "2019-03-17"
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":200}', $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00145577']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '320080']);

        $availabilities = $this->getRepository(Availability::class)
            ->findByDateRange($partner, new \DateTime('2019-03-14'), new \DateTime('2019-03-17'), [$product]);
        $this->assertCount(4, $availabilities);
        foreach ($availabilities as $availability) {
            $this->assertTrue($availability->isStopSale());
        }

        $originalValue = array_filter($availabilities,
            function($availability) {
                return $availability->getStock() > 0;
            }
        );
        $this->assertCount(3, $originalValue);

        $newValue = array_filter($availabilities,
            function($availability) {
                return $availability->getStock() == 0;
            }
        );
        $this->assertCount(1, $newValue);
    }

    public function testUpdateDataOperationInvalidDate()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Wrong date format. Expected format is `Y-m-d`"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationInvalidRestrictionFrom()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                            "restrictions" => array(
                                array(
                                    "minstay" => 1,
                                    "room_id" => "320080",
                                    "closed" => true,
                                    "rate_id" => "SBX",
                                    "dfrom" => "2019-09-",
                                    "dto" => "2019-09-27"
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Wrong date format. Expected format is `Y-m-d`"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationInvalidRestrictionTo()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                            "restrictions" => array(
                                array(
                                    "minstay" => 1,
                                    "room_id" => "320080",
                                    "closed" => true,
                                    "rate_id" => "SBX",
                                    "dfrom" => "2019-09-22",
                                    "dto" => "2019-09-"
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Wrong date format. Expected format is `Y-m-d`"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationNoRoomInRestriction()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                            "restrictions" => array(
                                array(
                                    "minstay" => 1,
                                    "closed" => true,
                                    "rate_id" => "SBX",
                                    "dfrom" => "2019-09-22",
                                    "dto" => "2019-09-27"
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Room id is not defined"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationWrongRoomInRestriction()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                            "restrictions" => array(
                                array(
                                    "minstay" => 1,
                                    "closed" => true,
                                    "room_id" => "00000000",
                                    "rate_id" => "SBX",
                                    "dfrom" => "2019-09-22",
                                    "dto" => "2019-09-27"
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":410,"error":"The product code `00000000` for Partner `00145577` is not registered in SBX Channel Manager."}', $response);
        $this->assertEquals(Response::HTTP_GONE, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationRestrictionFromGreaterThanTo()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                            "restrictions" => array(
                                array(
                                    "minstay" => 1,
                                    "closed" => true,
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "dfrom" => "2019-09-29",
                                    "dto" => "2019-09-27"
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Start date cannot be greater than end date"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationMissingRestrictionFrom()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                            "restrictions" => array(
                                array(
                                    "minstay" => 1,
                                    "closed" => true,
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "dto" => "2019-09-27"
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Dates must be defined"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationMissingRestrictionTo()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                            "restrictions" => array(
                                array(
                                    "minstay" => 1,
                                    "closed" => true,
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "dfrom" => "2019-09-27"
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Dates must be defined"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationNoPriceAmount()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Price can\u0027t be an empty value"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationNoStock()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080"
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Stock can\u0027t be an empty value"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationStockUnderZero()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => -2
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Availability cannot be less than 0"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationPriceUnderZero()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => -2
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Amount cannot be less than 0"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationInvalidRoom()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "3280",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 90.15
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":410,"error":"The product code `3280` for Partner `00145577` is not registered in SBX Channel Manager."}', $response);
        $this->assertEquals(410, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationNoRoom()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom" => "2019-09-01",
                                    "dto"   => "2019-09-07",
                                    "avail" => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 90.15
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Room id is not defined"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationStartDateGreaterThanEndDate()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-07",
                                    "dto"     => "2019-09-01",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 90.15
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Start date cannot be greater than end date"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateDataOperationNoDate()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "update_data",
                    "data"       =>
                        array(
                            "availability" => array(
                                array(
                                    "dfrom"   => "2019-09-07",
                                    "room_id" => "320080",
                                    "avail"   => 0
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "avail"   => 0
                                ),
                            ),
                            "prices"       => array(
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "366455",
                                    "rate_id" => "SBX",
                                    "price"   => 90.15
                                ),
                                array(
                                    "dfrom"   => "2019-09-01",
                                    "dto"     => "2019-09-07",
                                    "room_id" => "320080",
                                    "rate_id" => "SBX",
                                    "price"   => 106.45
                                ),
                            ),
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Dates must be defined"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testGetDataOperation()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "get_data",
                    "data"       =>
                        array(
                            "start_date" => "2019-12-01",
                            "end_date"   => "2019-12-02",
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":200,"data":{"hotel_id":"00145577","rooms":[{"room_id":"320080","days":{"2019-12-01":{"availability":0,"rates":[{"rate_id":"SBX","price":0}]},"2019-12-02":{"availability":0,"rates":[{"rate_id":"SBX","price":0}]}}},{"room_id":"366455","days":{"2019-12-01":{"availability":0,"rates":[{"rate_id":"SBX","price":0}]},"2019-12-02":{"availability":0,"rates":[{"rate_id":"SBX","price":0}]}}}]}}', $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);
    }

    public function testGetDataOperationWrongStartDateFormat()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "get_data",
                    "data"       =>
                        array(
                            "start_date" => "2019-31-12",
                            "end_date"   => "2019-12-02",
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Start date format has to be Y-m-d"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testGetDataOperationStartDateNotSent()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "get_data",
                    "data"       =>
                        array(
                            "end_date" => "2019-12-02",
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Dates must be defined"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testGetDataOperationStartDateIsEmpty()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "get_data",
                    "data"       =>
                        array(
                            "start_date" => "",
                            "end_date"   => "2019-12-02",
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();
        $this->assertContains('{"code":400,"error":"Start date format has to be Y-m-d"}', $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testGetBookingsOperation()
    {
        $this->client->request(
            'POST',
            '/api/wubook',
            [],
            [],
            [
                "CONTENT_TYPE" => "application/json",
            ],
            json_encode(
                array(
                    "cm_auth"    =>
                        array(
                            "username" => "wubook",
                            "password" => "password",
                        ),
                    "hotel_auth" =>
                        array(
                            "hotel_id" => "00145577",
                        ),
                    "action"     => "get_bookings",
                    "data"       =>
                        array(
                            "start_time" => "2018-08-31 15:00:00",
                        ),
                )
            )
        );

        $response = $this->client->getResponse()->getContent();

        $this->assertContains('"code":200', $response);
        $this->assertContains('"booking_id":"RESA-0009564511"', $response);
        $this->assertContains('"hotel_id":"00145577"', $response);
        $this->assertContains('"room_id":"320080"', $response);
        $this->assertContains('"guests":["Ana","Andres"]', $response);
        $this->assertContains('"customer":{"first_name":"Ana"', $response);
        $this->assertContains('"booking_id":"RESA-0009564512"', $response);
        $this->assertContains('"hotel_id":"00145577"', $response);
        $this->assertContains('"last_name":"Tomia"', $response);
        $this->assertTrue($this->client->getResponse()->isSuccessful(), $response);
    }
}