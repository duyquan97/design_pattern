<?php

namespace App\Tests\Controller;

use App\Entity\Booking;
use App\Entity\Partner;
use App\Entity\Product;
use App\Service\BookingEngineManager;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class StandardControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function testWsdl()
    {
        $this->client->request('GET', '/api/ext/soap/ota/v2/', [], [], [], '');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testHotelAvailNotifOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                  xmlns:soap=\"http://www.w3.org/2003/05/soap-envelope\"
                  xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                  xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                  <soap:Header>
                    <wss:Security soap:mustUnderstand = \"1\">
                      <wss:UsernameToken>
                        <wss:Username>availpro</wss:Username>
                        <wss:Password>password</wss:Password>
                      </wss:UsernameToken>
                    </wss:Security>
                  </soap:Header>
                <soap:Body>
                <OTA_HotelAvailNotifRQ xmlns=\"http://www.opentravel.org/OTA/2003/05\" Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                  <AvailStatusMessages HotelCode=\"00127978\">
                    <AvailStatusMessage BookingLimit=\"3\">
                      <StatusApplicationControl Start=\"2010-01-01\" End=\"2010-01-01\" InvTypeCode=\"409904\" RatePlanCode=\"SBX\" />
                      <RestrictionStatus Status=\"Open\" />
                    </AvailStatusMessage>
                    <AvailStatusMessage BookingLimit=\"8\">
                      <StatusApplicationControl Start=\"2010-01-02\" End=\"2010-01-02\" InvTypeCode=\"409904\" RatePlanCode=\"SBX\" />
                      <RestrictionStatus Status=\"Open\" />
                    </AvailStatusMessage>
                    <AvailStatusMessage>
                      <StatusApplicationControl Start=\"2010-01-03\" End=\"2010-01-03\" InvTypeCode=\"409904\" RatePlanCode=\"SBX\" />
                      <RestrictionStatus Status=\"Open\" />
                    </AvailStatusMessage>
                   </AvailStatusMessages>
                </OTA_HotelAvailNotifRQ>
                </soap:Body>
                </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testHotelAvailOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                    xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                    xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                    xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                        <soap:Header>
                            <wss:Security soap:mustUnderstand = \"1\">
                                <wss:UsernameToken>
                                    <wss:Username>availpro</wss:Username>
                                    <wss:Password>password</wss:Password>
                                </wss:UsernameToken>
                            </wss:Security>
                        </soap:Header>
                    <soap:Body>
                        <ota:OTA_HotelAvailRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                            <ota:AvailRequestSegments>
                                <ota:AvailRequestSegment AvailReqType = \"Room\">
                                    <ota:HotelSearchCriteria>
                                        <ota:Criterion>
                                            <ota:HotelRef HotelCode = \"00127978\"/>
                                        </ota:Criterion>
                                    </ota:HotelSearchCriteria>
                                </ota:AvailRequestSegment>
                            </ota:AvailRequestSegments>
                        </ota:OTA_HotelAvailRQ>
                    </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();


        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
        $this->assertContains('<ns2:RoomType RoomTypeCode="409904"><ns2:RoomDescription Name="Standard room 409904"/></ns2:RoomType>',
            $response);
        $this->assertContains('<ns2:RatePlanDescription Name="Smartbox Standard Rate"/>', $response);
        $this->assertContains('<ns2:RoomType RoomTypeCode="396872"><ns2:RoomDescription Name="Suite room 396872"/></ns2:RoomType>',
            $response);
    }

    public function testHotelAvailOperationTwo()
    {
        $xml = '<SOAP-ENV:Envelope
                    xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
                    <SOAP-ENV:Header>
                        <wsse:Security
                            xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
                            xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" soap:mustUnderstand="1">
                            <wsse:UsernameToken>
                                <wsse:Username>availpro</wsse:Username>
                                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">password</wsse:Password>
                            </wsse:UsernameToken>
                        </wsse:Security>
                    </SOAP-ENV:Header>
                    <SOAP-ENV:Body>
                        <OTA_HotelAvailRQ
                            xmlns="http://www.opentravel.org/OTA/2003/05" AvailRatesOnly="true" EchoToken="abc123" TimeStamp="2017-10-09T09:41:12+11:00" Version="1.0">
                            <AvailRequestSegments>
                                <AvailRequestSegment AvailReqType="Room">
                                    <HotelSearchCriteria>
                                        <Criterion>
                                            <HotelRef HotelCode="00127978"/>
                                        </Criterion>
                                    </HotelSearchCriteria>
                                </AvailRequestSegment>
                            </AvailRequestSegments>
                        </OTA_HotelAvailRQ>
                    </SOAP-ENV:Body>
                </SOAP-ENV:Envelope>';


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testHotelInvCountNotifOperation()
    {
        $randomStock = rand(10, 30);
        $randomStock2 = rand(10, 30);
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                    xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                    xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                    xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                        <soap:Header>
                            <wss:Security soap:mustUnderstand = \"1\">
                                <wss:UsernameToken>
                                    <wss:Username>availpro</wss:Username>
                                    <wss:Password>password</wss:Password>
                                </wss:UsernameToken>
                            </wss:Security>
                        </soap:Header>
                    <soap:Body>
                        <ota:OTA_HotelInvCountNotifRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                            <ota:Inventories HotelCode = \"00127978\">
                                <ota:Inventory>
                                    <ota:StatusApplicationControl Start=\"2025-09-13\" End=\"2025-09-21\" RatePlanCode = \"SBX\" InvTypeCode = \"409904\" IsRoom = \"true\"/>
                                    <ota:InvCounts>
                                        <ota:InvCount CountType=\"2\" Count=\"$randomStock\"/>
                                    </ota:InvCounts>
                                </ota:Inventory>
                                <ota:Inventory>
                                    <ota:StatusApplicationControl Start=\"2025-09-22\" End=\"2025-09-26\" RatePlanCode = \"SBX\" InvTypeCode = \"409904\" IsRoom = \"true\"/>
                                    <ota:InvCounts>
                                        <ota:InvCount CountType = \"2\" Count= \"$randomStock2\"/>
                                    </ota:InvCounts>
                                </ota:Inventory>
                            </ota:Inventories>
                        </ota:OTA_HotelInvCountNotifRQ>
                    </soap:Body>
                </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00127978']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '409904']);
        $availabilities = $this->getContainer()->get(BookingEngineManager::class)->getAvailabilities($partner,
            new \DateTime('2025-09-13'), new \DateTime('2025-09-21'), [$product]);
        $this->assertGreaterThan(0, sizeof($availabilities->getProductAvailabilities()));
        foreach ($availabilities->getProductAvailabilities() as $productAvailability) {
            foreach ($productAvailability->getAvailabilities() as $availability) {
                $this->assertEquals($randomStock, $availability->getStock());
            }
        }
        $availabilities = $this->getContainer()->get(BookingEngineManager::class)->getAvailabilities($partner,
            new \DateTime('2025-09-22'), new \DateTime('2025-09-26'), [$product]);
        $this->assertGreaterThan(0, sizeof($availabilities->getProductAvailabilities()));
        foreach ($availabilities->getProductAvailabilities() as $productAvailability) {
            foreach ($productAvailability->getAvailabilities() as $availability) {
                $this->assertEquals($randomStock2, $availability->getStock());
            }
        }
    }

    public function testHotelInvCountOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                  xmlns:soap=\"http://www.w3.org/2003/05/soap-envelope\"
                  xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                  xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                  <soap:Header>
                    <wss:Security soap:mustUnderstand = \"1\">
                      <wss:UsernameToken>
                        <wss:Username>availpro</wss:Username>
                        <wss:Password>password</wss:Password>
                      </wss:UsernameToken>
                    </wss:Security>
                  </soap:Header>
                  <soap:Body>
                    <ota:OTA_HotelInvCountRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                        <ota:HotelInvCountRequests>
                            <ota:HotelInvCountRequest>
                                <ota:HotelRef HotelCode = \"00127978\" />
                                <ota:DateRange Start = \"2017-04-14\" End = \"2018-04-14\"/>
                                <ota:RoomTypeCandidates>
                                    <ota:RoomTypeCandidate RoomTypeCode = \"409904\"/>
                                    <ota:RoomTypeCandidate RoomTypeCode = \"409904\"/>
                                </ota:RoomTypeCandidates>
                            </ota:HotelInvCountRequest>
                        </ota:HotelInvCountRequests>
                    </ota:OTA_HotelInvCountRQ>
                  </soap:Body>
                </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testHotelRateAmountNotifOperation()
    {
        $randomAmount = rand(1000, 9999) / 100;
        $randomAmount2 = rand(1000, 9999) / 100;
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                    xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                    xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                    xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                        <soap:Header>
                            <wss:Security soap:mustUnderstand = \"1\">
                                <wss:UsernameToken>
                                    <wss:Username>availpro</wss:Username>
                                    <wss:Password>password</wss:Password>
                                </wss:UsernameToken>
                            </wss:Security>
                        </soap:Header>
                    <soap:Body>
                        <ota:OTA_HotelRateAmountNotifRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                            <ota:RateAmountMessages HotelCode = \"00127978\">
                                <ota:RateAmountMessage>
                                    <ota:StatusApplicationControl RatePlanCode = \"SBX\" InvTypeCode =\"409904\" IsRoom = \"true\"/>
                                    <ota:Rates>
                                        <ota:Rate Start = \"2025-09-01\" End = \"2025-10-03\" CurrencyCode=\"EUR\">
                                            <ota:BaseByGuestAmts>
                                                <ota:BaseByGuestAmt AmountAfterTax = \"$randomAmount\"/>
                                            </ota:BaseByGuestAmts>
                                        </ota:Rate>
                                        <ota:Rate Start = \"2026-09-01\" End = \"2026-10-03\" CurrencyCode=\"EUR\">
                                            <ota:BaseByGuestAmts>
                                                <ota:BaseByGuestAmt AmountAfterTax = \"$randomAmount2\"/>
                                            </ota:BaseByGuestAmts>
                                        </ota:Rate>
                                    </ota:Rates>
                                </ota:RateAmountMessage>
                            </ota:RateAmountMessages>
                        </ota:OTA_HotelRateAmountNotifRQ>
                    </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00127978']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '409904']);

        $rates = $this->getContainer()->get(BookingEngineManager::class)->getRates($partner,
            new \DateTime('2025-09-01'), new \DateTime('2025-10-03'), [$product]);
        $this->assertGreaterThan(0, sizeof($rates->getProductRates()));
        foreach ($rates->getProductRates() as $productRate) {
            foreach ($productRate->getRates() as $rate) {
                $this->assertEquals($randomAmount, $rate->getAmount());
            }
        }

        $rates = $this->getContainer()->get(BookingEngineManager::class)->getRates($partner,
            new \DateTime('2026-09-01'), new \DateTime('2026-10-03'), [$product]);
        $this->assertGreaterThan(0, sizeof($rates->getProductRates()));
        foreach ($rates->getProductRates() as $productRate) {
            foreach ($productRate->getRates() as $rate) {
                $this->assertEquals($randomAmount2, $rate->getAmount());
            }
        }
    }

    public function testHotelRateAmountNotifOperationInvalidStatusApplicationControl()
    {
        $randomAmount = rand(1000, 9999) / 100;
        $randomAmount2 = rand(1000, 9999) / 100;
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                    xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                    xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                    xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                        <soap:Header>
                            <wss:Security soap:mustUnderstand = \"1\">
                                <wss:UsernameToken>
                                    <wss:Username>availpro</wss:Username>
                                    <wss:Password>password</wss:Password>
                                </wss:UsernameToken>
                            </wss:Security>
                        </soap:Header>
                    <soap:Body>
                        <ota:OTA_HotelRateAmountNotifRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                            <ota:RateAmountMessages HotelCode = \"00127978\">
                                <ota:RateAmountMessage>
                                    <ota:StatusApplicationControl RatePlanCode = \"SBX\" InvTypeCode =\"409904\" IsRoom = \"true\"/>
                                    <ota:Rates>
                                        <ota:Rate Start = \"2025-09-01\" End = \"2025-10-03\" CurrencyCode=\"EUR\">
                                            <ota:BaseByGuestAmts>
                                                <ota:BaseByGuestAmt AmountAfterTax = \"$randomAmount\"/>
                                            </ota:BaseByGuestAmts>
                                        </ota:Rate>
                                        <ota:Rate Start = \"2026-09-01\" End = \"2026-10-03\" CurrencyCode=\"EUR\">
                                            <ota:BaseByGuestAmts>
                                                <ota:BaseByGuestAmt AmountAfterTax = \"$randomAmount2\"/>
                                            </ota:BaseByGuestAmts>
                                        </ota:Rate>
                                    </ota:Rates>
                                    <ota:StatusApplicationControl RatePlanCode = \"SBX\" InvTypeCode =\"409904\" IsRoom = \"true\"/>
                                </ota:RateAmountMessage>
                            </ota:RateAmountMessages>
                        </ota:OTA_HotelRateAmountNotifRQ>
                    </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Error Code="400">"InvTypeCode" is mandatory</ns2:Error>', $response);
    }

    public function testHotelRateAmountNotifOperationMissingStart()
    {
        $randomAmount = rand(1000, 9999) / 100;
        $randomAmount2 = rand(1000, 9999) / 100;
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                    xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                    xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                    xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                        <soap:Header>
                            <wss:Security soap:mustUnderstand = \"1\">
                                <wss:UsernameToken>
                                    <wss:Username>availpro</wss:Username>
                                    <wss:Password>password</wss:Password>
                                </wss:UsernameToken>
                            </wss:Security>
                        </soap:Header>
                    <soap:Body>
                        <ota:OTA_HotelRateAmountNotifRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                            <ota:RateAmountMessages HotelCode = \"00127978\">
                                <ota:RateAmountMessage>
                                    <ota:StatusApplicationControl RatePlanCode = \"SBX\" InvTypeCode =\"409904\" IsRoom = \"true\"/>
                                    <ota:Rates>
                                        <ota:Rate End = \"2025-10-03\" CurrencyCode=\"EUR\">
                                            <ota:BaseByGuestAmts>
                                                <ota:BaseByGuestAmt AmountAfterTax = \"$randomAmount\"/>
                                            </ota:BaseByGuestAmts>
                                        </ota:Rate>
                                        <ota:Rate Start = \"2026-09-01\" End = \"2026-10-03\" CurrencyCode=\"EUR\">
                                            <ota:BaseByGuestAmts>
                                                <ota:BaseByGuestAmt AmountAfterTax = \"$randomAmount2\"/>
                                            </ota:BaseByGuestAmts>
                                        </ota:Rate>
                                    </ota:Rates>
                                </ota:RateAmountMessage>
                            </ota:RateAmountMessages>
                        </ota:OTA_HotelRateAmountNotifRQ>
                    </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Error Code="400">"Start" and "End" are mandatory</ns2:Error>', $response);
    }

    public function testHotelRatePlanOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                    xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                    xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                    xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                        <soap:Header>
                            <wss:Security soap:mustUnderstand = \"1\">
                                <wss:UsernameToken>
                                    <wss:Username>availpro</wss:Username>
                                    <wss:Password>password</wss:Password>
                                </wss:UsernameToken>
                            </wss:Security>
                        </soap:Header>
                    <soap:Body>
                        <ota:OTA_HotelRatePlanRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                            <ota:RatePlans>
                                <ota:RatePlan>
                                    <ota:HotelRef HotelCode = \"00127978\"/>
                                    <ota:DateRange Start = \"2050-09-01\" End = \"2050-09-07\"/>
                                    <ota:RatePlanCandidates>
                                        <ota:RatePlanCandidate RatePlanCode = \"SBX\"/>
                                    </ota:RatePlanCandidates>
                                </ota:RatePlan>
                            </ota:RatePlans>
                        </ota:OTA_HotelRatePlanRQ>
                    </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testReadWrongDateTypeOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                    <soap:Header>
                        <wss:Security soap:mustUnderstand = \"1\">
                            <wss:UsernameToken>
                                <wss:Username>availpro</wss:Username>
                                <wss:Password>password</wss:Password>
                            </wss:UsernameToken>
                        </wss:Security>
                    </soap:Header>
                <soap:Body>
                    <ota:OTA_ReadRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                        <ota:ReadRequests>
                            <ota:HotelReadRequest HotelCode = \"00127978\">
                                <ota:SelectionCriteria Start = \"2050-09-01\" End = \"2050-09-30\" DateType =\"WrongDateType\"/>
                            </ota:HotelReadRequest>
                        </ota:ReadRequests>
                    </ota:OTA_ReadRQ>
                </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('ns2:Errors', $response);
    }

    public function testReadLastUpdateDateInRangeOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                    <soap:Header>
                        <wss:Security soap:mustUnderstand = \"1\">
                            <wss:UsernameToken>
                                <wss:Username>availpro</wss:Username>
                                <wss:Password>password</wss:Password>
                            </wss:UsernameToken>
                        </wss:Security>
                    </soap:Header>
                <soap:Body>
                    <ota:OTA_ReadRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                        <ota:ReadRequests>
                            <ota:HotelReadRequest HotelCode = \"00127978\">
                                <ota:SelectionCriteria Start = \"2019-03-10\" End = \"2019-03-20\" DateType =\"LastUpdateDate\"/>
                            </ota:HotelReadRequest>
                        </ota:ReadRequests>
                    </ota:OTA_ReadRQ>
                </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $booking = $this->getRepository(Booking::class)->findOneBy(['identifier' => 'RESA-0009564497']);
        $transaction = $booking->getTransaction();

        $this->assertNotNull($transaction);

        $this->assertContains('ns2:HotelReservation', $response);
        $this->assertContains('RESA-0009564497', $response);
        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testReadLastUpdateDateOutRangeOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                    <soap:Header>
                        <wss:Security soap:mustUnderstand = \"1\">
                            <wss:UsernameToken>
                                <wss:Username>availpro</wss:Username>
                                <wss:Password>password</wss:Password>
                            </wss:UsernameToken>
                        </wss:Security>
                    </soap:Header>
                <soap:Body>
                    <ota:OTA_ReadRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                        <ota:ReadRequests>
                            <ota:HotelReadRequest HotelCode = \"00127978\">
                                <ota:SelectionCriteria Start = \"2020-03-10\" End = \"2020-03-20\" DateType =\"LastUpdateDate\"/>
                            </ota:HotelReadRequest>
                        </ota:ReadRequests>
                    </ota:OTA_ReadRQ>
                </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertNotContains('ns2:HotelReservation', $response);
        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testReadCreateDateInRangeOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                    <soap:Header>
                        <wss:Security soap:mustUnderstand = \"1\">
                            <wss:UsernameToken>
                                <wss:Username>availpro</wss:Username>
                                <wss:Password>password</wss:Password>
                            </wss:UsernameToken>
                        </wss:Security>
                    </soap:Header>
                <soap:Body>
                    <ota:OTA_ReadRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                        <ota:ReadRequests>
                            <ota:HotelReadRequest HotelCode = \"00127978\">
                                <ota:SelectionCriteria Start = \"2019-03-16\" End = \"2019-03-20\" DateType =\"CreateDate\"/>
                            </ota:HotelReadRequest>
                        </ota:ReadRequests>
                    </ota:OTA_ReadRQ>
                </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $booking = $this->getRepository(Booking::class)->findOneBy(['identifier' => 'RESA-0009564498']);
        $transaction = $booking->getTransaction();

        $this->assertNotNull($transaction);

        $this->assertContains('ns2:HotelReservation', $response);
        $this->assertContains('RESA-0009564498', $response);
        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testReadCreateDateOutRangeOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                    <soap:Header>
                        <wss:Security soap:mustUnderstand = \"1\">
                            <wss:UsernameToken>
                                <wss:Username>availpro</wss:Username>
                                <wss:Password>password</wss:Password>
                            </wss:UsernameToken>
                        </wss:Security>
                    </soap:Header>
                <soap:Body>
                    <ota:OTA_ReadRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                        <ota:ReadRequests>
                            <ota:HotelReadRequest HotelCode = \"00127978\">
                                <ota:SelectionCriteria Start = \"2020-03-11\" End = \"2020-03-20\" DateType =\"CreateDate\"/>
                            </ota:HotelReadRequest>
                        </ota:ReadRequests>
                    </ota:OTA_ReadRQ>
                </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertNotContains('ns2:HotelReservation', $response);
        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testReadArrivalDateInRangeOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                    <soap:Header>
                        <wss:Security soap:mustUnderstand = \"1\">
                            <wss:UsernameToken>
                                <wss:Username>availpro</wss:Username>
                                <wss:Password>password</wss:Password>
                            </wss:UsernameToken>
                        </wss:Security>
                    </soap:Header>
                <soap:Body>
                    <ota:OTA_ReadRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                        <ota:ReadRequests>
                            <ota:HotelReadRequest HotelCode = \"00127978\">
                                <ota:SelectionCriteria Start = \"2019-03-11\" End = \"2019-03-30\" DateType =\"ArrivalDate\"/>
                            </ota:HotelReadRequest>
                        </ota:ReadRequests>
                    </ota:OTA_ReadRQ>
                </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $booking = $this->getRepository(Booking::class)->findOneBy(['identifier' => 'RESA-0009564497']);
        $transaction = $booking->getTransaction();

        $this->assertNotNull($transaction);

        $this->assertContains('ns2:HotelReservation', $response);
        $this->assertContains('RESA-0009564497', $response);
        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testReadArrivalDateOutRangeOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                    <soap:Header>
                        <wss:Security soap:mustUnderstand = \"1\">
                            <wss:UsernameToken>
                                <wss:Username>availpro</wss:Username>
                                <wss:Password>password</wss:Password>
                            </wss:UsernameToken>
                        </wss:Security>
                    </soap:Header>
                <soap:Body>
                    <ota:OTA_ReadRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                        <ota:ReadRequests>
                            <ota:HotelReadRequest HotelCode = \"00127978\">
                                <ota:SelectionCriteria Start = \"2020-03-11\" End = \"2020-03-30\" DateType =\"ArrivalDate\"/>
                            </ota:HotelReadRequest>
                        </ota:ReadRequests>
                    </ota:OTA_ReadRQ>
                </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertNotContains('ns2:HotelReservation', $response);
        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testReadDepartureDateInRangeOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                    <soap:Header>
                        <wss:Security soap:mustUnderstand = \"1\">
                            <wss:UsernameToken>
                                <wss:Username>availpro</wss:Username>
                                <wss:Password>password</wss:Password>
                            </wss:UsernameToken>
                        </wss:Security>
                    </soap:Header>
                <soap:Body>
                    <ota:OTA_ReadRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                        <ota:ReadRequests>
                            <ota:HotelReadRequest HotelCode = \"00127978\">
                                <ota:SelectionCriteria Start = \"2019-05-11\" End = \"2019-05-20\" DateType =\"DepartureDate\"/>
                            </ota:HotelReadRequest>
                        </ota:ReadRequests>
                    </ota:OTA_ReadRQ>
                </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $booking = $this->getRepository(Booking::class)->findOneBy(['identifier' => 'RESA-0009564498']);
        $transaction = $booking->getTransaction();

        $this->assertNotNull($transaction);

        $this->assertContains('ns2:HotelReservation', $response);
        $this->assertContains('RESA-0009564498', $response);
        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testReadDepartureDateOutRangeOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                    <soap:Header>
                        <wss:Security soap:mustUnderstand = \"1\">
                            <wss:UsernameToken>
                                <wss:Username>availpro</wss:Username>
                                <wss:Password>password</wss:Password>
                            </wss:UsernameToken>
                        </wss:Security>
                    </soap:Header>
                <soap:Body>
                    <ota:OTA_ReadRQ Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                        <ota:ReadRequests>
                            <ota:HotelReadRequest HotelCode = \"00127978\">
                                <ota:SelectionCriteria Start = \"2020-03-11\" End = \"2020-03-30\" DateType =\"DepartureDate\"/>
                            </ota:HotelReadRequest>
                        </ota:ReadRequests>
                    </ota:OTA_ReadRQ>
                </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertNotContains('ns2:HotelReservation', $response);
        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('EchoToken="abc123"', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
    }

    public function testPingOperation()
    {
        $randomMessage = substr(md5(mt_rand()), 0, 12);
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                <soap:Body>
                    <ota:OTA_PingRQ>
                        <ota:EchoData>$randomMessage</ota:EchoData>
                    </ota:OTA_PingRQ>
                </soap:Body>
                </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns1:Success/>', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
        $this->assertContains('<ns1:EchoData>' . $randomMessage . '</ns1:EchoData>', $response);
    }

    public function testErrorResponse()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                    <soap:Header>
                        <wss:Security soap:mustUnderstand = \"1\">
                            <wss:UsernameToken>
                                <wss:Username>PAR00054714</wss:Username>
                                <wss:Password>PAR00054714</wss:Password>
                            </wss:UsernameToken>
                        </wss:Security>
                    </soap:Header>
                <soap:Body>
                    <ota:OTA_ReadRQ>
                        <ota:ReadRequests>
                            <ota:HotelReadRequest HotelCode = \"wrong\">
                                <ota:SelectionCriteria Start = \"2050-09-01\" End = \"2050-09-30\" DateType =\"LastUpdateDate\"/>
                            </ota:HotelReadRequest>
                        </ota:ReadRequests>
                    </ota:OTA_ReadRQ>
                </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('OTA_ResRetrieveRS', $response);
        $this->assertContains('<ns2:Errors><ns2:Error Code="403">Access Denied</ns2:Error></ns2:Errors>', $response);
    }

    public function testHotelAvailGetOperation()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope
  xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
  xmlns:wss = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
  xmlns:ota = "http://www.opentravel.org/OTA/2003/05">
    <soap:Header>
        <wss:Security soap:mustUnderstand = "1">
            <wss:UsernameToken>
                <wss:Username>yieldplanet</wss:Username>
                <wss:Password>password</wss:Password>
            </wss:UsernameToken>
        </wss:Security>
    </soap:Header>
    <soap:Body>
        <ota:OTA_HotelAvailGetRQ xmlns="http://www.opentravel.org/OTA/2003/05" Version="1.0" TimeStamp="2016-05-26T11:11:50-04:00" EchoToken="001-1466531393">
            <ota:HotelAvailRequests>
                <ota:HotelAvailRequest>
                    <ota:HotelRef HotelCode="00019091" />
                    <ota:DateRange Start="2017-01-01" End="2017-01-03" />
                    <ota:RatePlanCandidates>
                        <ota:RatePlanCandidate RatePlanCode="SBX" />
                    </ota:RatePlanCandidates>
                </ota:HotelAvailRequest>
            </ota:HotelAvailRequests>
        </ota:OTA_HotelAvailGetRQ>
    </soap:Body>
</soap:Envelope>';


        $this->client->request('POST', '/api/ext/soap/ota/v2/', [], [], [], $xml);

        $response = $this->client->getResponse();
        $content = $response->getContent();
        $availableStatus = simplexml_load_string($content);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('OTA_HotelAvailGetRS', $content);
        $this->assertContains('<ns2:AvailStatusMessages HotelCode="00019091">', $content);
        $this->assertContains('<ns2:AvailStatusMessage><ns2:StatusApplicationControl Start="2017-01-01" End="2017-01-01" RatePlanCode="SBX" InvTypeCode="235854"/><ns2:RestrictionStatus Restriction="Master" Status="Open"/></ns2:AvailStatusMessage>', $content);
        $this->assertContains('<ns2:AvailStatusMessage><ns2:StatusApplicationControl Start="2017-01-01" End="2017-01-01" RatePlanCode="SBX" InvTypeCode="393333"/><ns2:RestrictionStatus Restriction="Master" Status="Close"/></ns2:AvailStatusMessage>', $content);

    }

}
