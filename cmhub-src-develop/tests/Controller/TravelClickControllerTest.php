<?php

namespace Tests\App\Controller;

use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\ProductRate;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TravelClickControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function testWsdl()
    {
        $this->client->request('GET', '/api/ext/soap/ota/travelclick', [], [], [], '');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testPingOperation()
    {
        $randomMessage = substr(md5(mt_rand()), 0, 12);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_PingRQ EchoToken="123" Version="1.0" TimeStamp="2016-05-26T11:11:50-04:00" xmlns="http://www.opentravel.org/OTA/2003/05">
                    <EchoData>'.$randomMessage.'</EchoData>
                </OTA_PingRQ>';


        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<Success/>', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('standalone="yes"', $response);
        $this->assertContains('TimeStamp="', $response);
        $this->assertContains('EchoToken="123"', $response);
        $this->assertContains('xmlns="http://www.opentravel.org/OTA/2003/05"', $response);
        $this->assertContains('<EchoData>'.$randomMessage.'</EchoData>', $response);
    }

    public function testAuthenticationFailed()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><OTA_ReadRQ></OTA_ReadRQ>';

        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], [], $xml);
        $response = $this->client->getResponse();

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testHotelAvailOperation()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_HotelAvailGetRQ Version="1.0"
                                     TimeStamp="2016-05-26T11:11:50-04:00"
                                     EchoToken="001-1466512393"
                                     xmlns="http://www.opentravel.org/OTA/2003/05">
                    <HotelAvailRequests>
                        <HotelAvailRequest>
                            <HotelRef HotelCode="00145205" />
                            <DateRange Start="2019-03-15" End="2019-03-17" />
                            <RatePlanCandidates>
                                <RatePlanCandidate RatePlanCode="SBX" />
                            </RatePlanCandidates>
                        </HotelAvailRequest>
                    </HotelAvailRequests>
                </OTA_HotelAvailGetRQ>';


        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], ['PHP_AUTH_USER' => 'travelclick', 'PHP_AUTH_PW' => 'password'], $xml);
        $response = $this->client->getResponse();
        $availableStatus = simplexml_load_string($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<Success/>', $response->getContent());
        $this->assertContains('Version="1.0"', $response->getContent());
        $this->assertContains('standalone="yes"', $response->getContent());
        $this->assertContains('TimeStamp="', $response->getContent());
        $this->assertContains('EchoToken="001-1466512393"', $response->getContent());
        $this->assertContains('<RestrictionStatus Restriction="Master" Status="Open"/>', $response->getContent());
        $this->assertContains('<RestrictionStatus Restriction="Master" Status="Close"/>', $response->getContent());
        $this->assertCount(6, $availableStatus->AvailStatusMessages->AvailStatusMessage);
        $this->assertContains('<StatusApplicationControl Start="2019-03-15" End="2019-03-15" RatePlanCode="SBX" InvTypeCode="328383"/>', $response->getContent());
        $this->assertContains('<StatusApplicationControl Start="2019-03-16" End="2019-03-16" RatePlanCode="SBX" InvTypeCode="328383"/>', $response->getContent());
        $this->assertContains('<StatusApplicationControl Start="2019-03-17" End="2019-03-17" RatePlanCode="SBX" InvTypeCode="328383"/>', $response->getContent());
        $this->assertContains('<StatusApplicationControl Start="2019-03-15" End="2019-03-15" RatePlanCode="SBX" InvTypeCode="463866"/>', $response->getContent());
        $this->assertContains('<StatusApplicationControl Start="2019-03-16" End="2019-03-16" RatePlanCode="SBX" InvTypeCode="463866"/>', $response->getContent());
        $this->assertContains('<StatusApplicationControl Start="2019-03-17" End="2019-03-17" RatePlanCode="SBX" InvTypeCode="463866"/>', $response->getContent());
    }

    public function testHotelAvailOperationForCandidate()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_HotelAvailGetRQ Version="1.0"
                                     TimeStamp="2016-05-26T11:11:50-04:00"
                                     EchoToken="001-1466512393"
                                     xmlns="http://www.opentravel.org/OTA/2003/05">
                    <HotelAvailRequests>
                        <HotelAvailRequest>
                            <HotelRef HotelCode="00145205"/>
                            <DateRange Start="2019-03-15" End="2019-03-16"/>
                            <RatePlanCandidates>
                                <RatePlanCandidate RatePlanCode="SBX"/>
                            </RatePlanCandidates>
                            <RoomTypeCandidates>
                                <RoomTypeCandidate RoomTypeCode="328383"/>
                            </RoomTypeCandidates>
                        </HotelAvailRequest>
                    </HotelAvailRequests>
                </OTA_HotelAvailGetRQ>';


        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], ['PHP_AUTH_USER' => 'travelclick', 'PHP_AUTH_PW' => 'password'], $xml);
        $response = $this->client->getResponse();
        $availableStatus = simplexml_load_string($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<Success/>', $response->getContent());
        $this->assertContains('Version="1.0"', $response->getContent());
        $this->assertContains('standalone="yes"', $response->getContent());
        $this->assertContains('TimeStamp="', $response->getContent());
        $this->assertContains('EchoToken="001-1466512393"', $response->getContent());
        $this->assertCount(2, $availableStatus->AvailStatusMessages->AvailStatusMessage);
        $this->assertContains('<StatusApplicationControl Start="2019-03-15" End="2019-03-15" RatePlanCode="SBX" InvTypeCode="328383"/><RestrictionStatus Restriction="Master" Status="Open"/>', $response->getContent());
        $this->assertContains('<StatusApplicationControl Start="2019-03-16" End="2019-03-16" RatePlanCode="SBX" InvTypeCode="328383"/><RestrictionStatus Restriction="Master" Status="Close"/>', $response->getContent());
    }

    public function testHotelAvailOperationForUnkownCandidate()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_HotelAvailGetRQ Version="1.0"
                                     TimeStamp="2016-05-26T11:11:50-04:00"
                                     EchoToken="001-1466512393"
                                     xmlns="http://www.opentravel.org/OTA/2003/05">
                    <HotelAvailRequests>
                        <HotelAvailRequest>
                            <HotelRef HotelCode="00145205"/>
                            <DateRange Start="2019-03-15" End="2019-03-15"/>
                            <RatePlanCandidates>
                                <RatePlanCandidate RatePlanCode="SBX"/>
                            </RatePlanCandidates>
                            <RoomTypeCandidates>
                                <RoomTypeCandidate RoomTypeCode="123456"/>
                            </RoomTypeCandidates>
                        </HotelAvailRequest>
                    </HotelAvailRequests>
                </OTA_HotelAvailGetRQ>';


        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], ['PHP_AUTH_USER' => 'travelclick', 'PHP_AUTH_PW' => 'password'], $xml);
        $response = $this->client->getResponse();
        $availableStatus = simplexml_load_string($response->getContent());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<Success/>', $response->getContent());
        $this->assertContains('Version="1.0"', $response->getContent());
        $this->assertContains('standalone="yes"', $response->getContent());
        $this->assertContains('TimeStamp="', $response->getContent());
        $this->assertContains('EchoToken="001-1466512393"', $response->getContent());
        $this->assertCount(2, $availableStatus->AvailStatusMessages->AvailStatusMessage);
        $this->assertContains('<StatusApplicationControl Start="2019-03-15" End="2019-03-15" RatePlanCode="SBX" InvTypeCode="328383"/><RestrictionStatus Restriction="Master" Status="Open"/>', $response->getContent());
        $this->assertContains('<StatusApplicationControl Start="2019-03-15" End="2019-03-15" RatePlanCode="SBX" InvTypeCode="463866"/><RestrictionStatus Restriction="Master" Status="Close"/>', $response->getContent());
    }

    public function testHotelProductOperation()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_HotelProductRQ Version="1.0"
                                    TimeStamp="2016-05-26T11:11:50-04:00"
                                    EchoToken="001-1466531393"
                                    xmlns="http://www.opentravel.org/OTA/2003/05">
                    <HotelProducts>
                        <HotelProduct HotelCode="00145205" />
                    </HotelProducts>
                </OTA_HotelProductRQ>';


        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], ['PHP_AUTH_USER' => 'travelclick', 'PHP_AUTH_PW' => 'password'], $xml);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<Success/>', $response->getContent());
        $this->assertContains('Version="1.0"', $response->getContent());
        $this->assertContains('standalone="yes"', $response->getContent());
        $this->assertContains('TimeStamp="', $response->getContent());
        $this->assertContains('EchoToken="001-1466531393"', $response->getContent());
        $this->assertContains('<RoomType RoomTypeName="Standard room 328383" RoomTypeCode="328383"/>', $response->getContent());
        $this->assertContains('<RoomType RoomTypeName="Suite room 463866" RoomTypeCode="463866"/>', $response->getContent());
    }

    public function testHotelInvCountOperation()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_HotelInvCountNotifRQ Version="1.0"
                                          TimeStamp="2016-05-26T11:11:50-04:00"
                                          EchoToken="001-1466531888"
                                          xmlns="http://www.opentravel.org/OTA/2003/05">
                    <Inventories HotelCode="00145205">
                        <Inventory>
                            <StatusApplicationControl Start="2017-01-15" End="2017-01-15" InvTypeCode="328383" RatePlanCode="SBX" />
                            <InvCounts>
                                <InvCount Count="25" CountType="2" />
                            </InvCounts>
                        </Inventory>
                    </Inventories>
                </OTA_HotelInvCountNotifRQ>';

        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], ['PHP_AUTH_USER' => 'travelclick', 'PHP_AUTH_PW' => 'password'], $xml);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<Success/>', $response->getContent());
        $this->assertContains('Version="1.0"', $response->getContent());
        $this->assertContains('standalone="yes"', $response->getContent());
        $this->assertContains('TimeStamp="', $response->getContent());

        $this->assertContains('EchoToken="001-1466531888"', $response->getContent());
    }

    public function testHotelRatePlanNotifOperation()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_HotelRatePlanNotifRQ Version="1.0"
                                          TimeStamp="2016-05-26T11:11:50-04:00"
                                          EchoToken="001-1466531393"
                                          xmlns="http://www.opentravel.org/OTA/2003/05">
                    <RatePlans HotelCode="00145205">
                        <RatePlan RatePlanCode="SBX" Start="2017-01-01" End="2017-01-15">
                            <Rates>
                                <Rate InvTypeCode="328383" CurrencyCode="EUR">
                                    <BaseByGuestAmts>
                                        <BaseByGuestAmt AmountAfterTax="100.00" />
                                    </BaseByGuestAmts>
                                </Rate>
                            </Rates>
                        </RatePlan>
                    </RatePlans>
                </OTA_HotelRatePlanNotifRQ>';

        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], ['PHP_AUTH_USER' => 'travelclick', 'PHP_AUTH_PW' => 'password'], $xml);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<Success/>', $response->getContent());
        $this->assertContains('Version="1.0"', $response->getContent());
        $this->assertContains('standalone="yes"', $response->getContent());
        $this->assertContains('TimeStamp="', $response->getContent());

        $this->assertContains('EchoToken="001-1466531393"', $response->getContent());
        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00145205']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '328383']);
        $rates = $this->getRepository(ProductRate::class)->findByDateRange(
            $partner,
            new \DateTime('2017-01-01'),
            new \DateTime('2017-01-14'),
            [$product]
        );

        foreach ($rates as $rate) {
            $this->assertEquals(100, $rate->getAmount());
        }
    }

    public function testHotelRatePlanNotifOperationBasedOnOccupancy()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_HotelRatePlanNotifRQ Version="1.0"
                                          TimeStamp="2016-05-26T11:11:50-04:00"
                                          EchoToken="001-1466531393"
                                          xmlns="http://www.opentravel.org/OTA/2003/05">
                    <RatePlans HotelCode="00145205">
                        <RatePlan RatePlanCode="SBX" Start="2017-01-01" End="2017-01-15">
                            <Rates>
                                <Rate InvTypeCode="328383" CurrencyCode="EUR">
                                    <BaseByGuestAmts>
                                        <BaseByGuestAmt AmountAfterTax="99.00" NumberOfGuests="1" AgeQualifyingCode="10"/>
                                        <BaseByGuestAmt AmountAfterTax="99.00" NumberOfGuests="2" AgeQualifyingCode="10"/>
                                    </BaseByGuestAmts>
                                </Rate>
                            </Rates>
                        </RatePlan>
                    </RatePlans>
                </OTA_HotelRatePlanNotifRQ>';

        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], ['PHP_AUTH_USER' => 'travelclick', 'PHP_AUTH_PW' => 'password'], $xml);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<Success/>', $response->getContent());
        $this->assertContains('Version="1.0"', $response->getContent());
        $this->assertContains('standalone="yes"', $response->getContent());
        $this->assertContains('TimeStamp="', $response->getContent());

        $this->assertContains('EchoToken="001-1466531393"', $response->getContent());
        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00145205']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '328383']);
        $rates = $this->getRepository(ProductRate::class)->findByDateRange(
            $partner,
            new \DateTime('2017-01-01'),
            new \DateTime('2017-01-14'),
            [$product]
        );

        foreach ($rates as $rate) {
            $this->assertEquals(99, $rate->getAmount());
        }
    }

    public function testHotelRatePlanNotifOperationWithout2Guest()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_HotelRatePlanNotifRQ Version="1.0"
                                          TimeStamp="2016-05-26T11:11:50-04:00"
                                          EchoToken="001-1466531393"
                                          xmlns="http://www.opentravel.org/OTA/2003/05">
                    <RatePlans HotelCode="00145205">
                        <RatePlan RatePlanCode="SBX" Start="2017-01-01" End="2017-01-15">
                            <Rates>
                                <Rate InvTypeCode="328383" CurrencyCode="EUR">
                                    <BaseByGuestAmts>
                                        <BaseByGuestAmt AmountAfterTax="30.00" NumberOfGuests="1" AgeQualifyingCode="10"/>
                                        <BaseByGuestAmt AmountAfterTax="30.00" NumberOfGuests="3" AgeQualifyingCode="10"/>
                                    </BaseByGuestAmts>
                                </Rate>
                            </Rates>
                        </RatePlan>
                    </RatePlans>
                </OTA_HotelRatePlanNotifRQ>';

        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], ['PHP_AUTH_USER' => 'travelclick', 'PHP_AUTH_PW' => 'password'], $xml);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<Errors>', $response->getContent());
        $this->assertContains('Code="400"', $response->getContent());
        $this->assertContains('standalone="yes"', $response->getContent());
        $this->assertContains('Can not consume price', $response->getContent());

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00145205']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '328383']);
        $rates = $this->getRepository(ProductRate::class)->findByDateRange(
            $partner,
            new \DateTime('2017-01-01'),
            new \DateTime('2017-01-14'),
            [$product]
        );

        foreach ($rates as $rate) {
            $this->assertNotEquals(30, $rate->getAmount());
        }
    }

    public function testHotelRatePlanOperation()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_HotelRatePlanRQ Version="1.0"
                                     TimeStamp="2016-05-26T11:11:50-04:00"
                                     EchoToken="001-1466531111"
                                     xmlns="http://www.opentravel.org/OTA/2003/05">
                    <RatePlans>
                        <RatePlan>
                            <HotelRef HotelCode="00145205" />
                            <DateRange Start="2017-01-01" End="2017-01-01" />
                            <RatePlanCandidates>
                                <RatePlanCandidate RatePlanCode="SBX" />
                            </RatePlanCandidates>
                        </RatePlan>
                    </RatePlans>
                </OTA_HotelRatePlanRQ>';

        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], ['PHP_AUTH_USER' => 'travelclick', 'PHP_AUTH_PW' => 'password'], $xml);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<Success/>', $response->getContent());
        $this->assertContains('Version="1.0"', $response->getContent());
        $this->assertContains('TimeStamp="', $response->getContent());
        $this->assertContains('standalone="yes"', $response->getContent());

        $this->assertContains('EchoToken="001-1466531111"', $response->getContent());
        $this->assertContains('<RatePlans HotelCode="00145205">', $response->getContent());
        $this->assertContains('<RatePlan Start="2017-01-01" End="2017-01-01" RatePlanCode="SBX">', $response->getContent());
        $this->assertContains('<Rate CurrencyCode="EUR" InvTypeCode="328383">', $response->getContent());
        $this->assertContains('<BaseByGuestAmt AmountAfterTax="99" NumberOfGuests="2"/>', $response->getContent());
    }

    public function testHotelAvailNotifOperation()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_HotelAvailNotifRQ Version="1.0"
                                       TimeStamp="2016-05-26T11:11:50-04:00"
                                       EchoToken="001-1466531113"
                                       xmlns="http://www.opentravel.org/OTA/2003/05">
                    <AvailStatusMessages HotelCode="00145205">
                        <AvailStatusMessage>
                            <StatusApplicationControl Start="2017-01-15" End="2017-01-16" InvTypeCode="328383" RatePlanCode="SBX" />
                            <LengthsOfStay ArrivalDateBased="true">
                                <LengthOfStay MinMaxMessageType="MinLOS" TimeUnit="Day" Time="3" />
                            </LengthsOfStay>
                        </AvailStatusMessage>
                        <AvailStatusMessage>
                            <StatusApplicationControl Start="2017-01-15" End="2017-01-16" InvTypeCode="463866" RatePlanCode="SBX" />
                            <RestrictionStatus Restriction="Master" Status="Open" />
                        </AvailStatusMessage>
                    </AvailStatusMessages>
                </OTA_HotelAvailNotifRQ>';

        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], ['PHP_AUTH_USER' => 'travelclick', 'PHP_AUTH_PW' => 'password'], $xml);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<Success/>', $response->getContent());
        $this->assertContains('Version="1.0"', $response->getContent());
        $this->assertContains('standalone="yes"', $response->getContent());
        $this->assertContains('TimeStamp="', $response->getContent());

        $this->assertContains('EchoToken="001-1466531113"', $response->getContent());
    }

    public function testHotelInvCountNotifOperation()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <OTA_HotelInvCountRQ Version="1.0"
                                     TimeStamp="2016-05-26T11:11:50-04:00"
                                     EchoToken="001-1466531293"
                                     xmlns="http://www.opentravel.org/OTA/2003/05">
                    <HotelInvCountRequests>
                        <HotelInvCountRequest>
                            <HotelRef HotelCode="00145205" />
                            <DateRange Start = "2018-04-16" End = "2018-04-16" />
                            <RoomTypeCandidates>
                                <RoomTypeCandidate RoomTypeCode="328383" />
                                <RoomTypeCandidate RoomTypeCode="463866" />
                            </RoomTypeCandidates>
                        </HotelInvCountRequest>
                    </HotelInvCountRequests>
                </OTA_HotelInvCountRQ>';

        $this->client->request('POST', '/api/ext/soap/ota/travelclick', [], [], ['PHP_AUTH_USER' => 'travelclick', 'PHP_AUTH_PW' => 'password'], $xml);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('<Success/>', $response->getContent());
        $this->assertContains('Version="1.0"', $response->getContent());
        $this->assertContains('TimeStamp="', $response->getContent());
        $this->assertContains('standalone="yes"', $response->getContent());

        $this->assertContains('EchoToken="001-1466531293"', $response->getContent());
        $this->assertContains('<StatusApplicationControl Start="2018-04-16" End="2018-04-16" RatePlanCode="SBX" InvTypeCode="463866" IsRoom="true"/>', $response->getContent());
        $this->assertContains('<InvCount CountType="2" Count="0"/>', $response->getContent());
    }

}
