<?php

namespace App\Tests\Controller;

use App\Entity\Availability;
use App\Entity\Partner;
use App\Entity\Product;
use App\Service\BookingEngineManager;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SiteminderControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function testSiteminderWsdl()
    {
        $this->client->request('GET', '/api/ext/soap/ota/siteminder', [], [], [], '');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testSiteminderHotelRateAmountNotifOperation()
    {
        $randomAmount = rand(1000, 9999) / 100;
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <soap:Envelope
                  xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                  xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                  xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                    <soap:Header>
                      <wss:Security soap:mustUnderstand = \"1\">
                        <wss:UsernameToken>
                          <wss:Username>siteminder</wss:Username>
                          <wss:Password>password</wss:Password>
                        </wss:UsernameToken>
                      </wss:Security>
                    </soap:Header>
                  <soap:Body>
                    <ota:OTA_HotelRateAmountNotifRQ>
                      <ota:RateAmountMessages HotelCode = \"00019371\">
                            <ota:RateAmountMessage>
                               <ota:StatusApplicationControl InvTypeCode=\"504963\" RatePlanCode=\"SBX\" Start=\"2019-01-01\" End=\"2019-01-01\" />
                               <ota:Rates>
                                  <ota:Rate>
                                     <ota:BaseByGuestAmts>
                                        <ota:BaseByGuestAmt AgeQualifyingCode=\"10\" AmountAfterTax=\"$randomAmount\" />
                                     </ota:BaseByGuestAmts>
                                  </ota:Rate>
                               </ota:Rates>
                            </ota:RateAmountMessage>
                            <ota:RateAmountMessage>
                               <ota:StatusApplicationControl InvTypeCode=\"504963\" RatePlanCode=\"SBX\" Start=\"2019-02-01\" End=\"2019-02-01\" />
                               <ota:Rates>
                                  <ota:Rate>
                                     <ota:BaseByGuestAmts>
                                        <ota:BaseByGuestAmt AgeQualifyingCode=\"10\" AmountAfterTax=\"$randomAmount\" />
                                     </ota:BaseByGuestAmts>
                                  </ota:Rate>
                               </ota:Rates>
                            </ota:RateAmountMessage>
                      </ota:RateAmountMessages>
                    </ota:OTA_HotelRateAmountNotifRQ>
                  </soap:Body>
                </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/siteminder', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Success/>', $response);

        /**
         * @var Partner $partner
         */
        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019371']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '504963']);

        $rates = $this->getContainer()->get(BookingEngineManager::class)->getRates($partner, new \DateTime('2019-01-01'), new \DateTime('2019-01-01'), [$product]);
        $this->assertGreaterThan(0, sizeof($rates->getProductRates()));
        foreach ($rates->getProductRates() as $productRate) {
            foreach ($productRate->getRates() as $rate) {
                $this->assertEquals($randomAmount, $rate->getAmount());
            }
        }
    }

    public function testSiteminderHotelAvailNotifOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                  xmlns:soap=\"http://www.w3.org/2003/05/soap-envelope\"
                  xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                  xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                  <soap:Header>
                    <wss:Security soap:mustUnderstand = \"1\">
                      <wss:UsernameToken>
                        <wss:Username>siteminder</wss:Username>
                        <wss:Password>password</wss:Password>
                      </wss:UsernameToken>
                    </wss:Security>
                  </soap:Header>
                <soap:Body>
                <OTA_HotelAvailNotifRQ xmlns=\"http://www.opentravel.org/OTA/2003/05\" Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                  <AvailStatusMessages HotelCode=\"00019371\">
                    <AvailStatusMessage BookingLimit=\"3\">
                      <StatusApplicationControl Start=\"2010-01-01\" End=\"2010-01-01\" InvTypeCode=\"504963\" RatePlanCode=\"SBX\" />
                      <RestrictionStatus Status=\"Open\" />
                    </AvailStatusMessage>
                    <AvailStatusMessage>
                      <StatusApplicationControl Start=\"2010-01-01\" End=\"2010-01-01\" InvTypeCode=\"504963\" RatePlanCode=\"SBX\" />
                      <LengthsOfStay> 
                        <LengthOfStay MinMaxMessageType=\"SetMinLOS\" Time=\"1\" /> 
                      </LengthsOfStay> 
                    </AvailStatusMessage>
                    <AvailStatusMessage BookingLimit=\"8\">
                      <StatusApplicationControl Start=\"2010-01-02\" End=\"2010-01-02\" InvTypeCode=\"504963\" RatePlanCode=\"SBX\" />
                      <RestrictionStatus Status=\"Open\" />
                    </AvailStatusMessage>
                    <AvailStatusMessage>
                      <StatusApplicationControl Start=\"2010-01-02\" End=\"2010-01-02\" InvTypeCode=\"504963\" RatePlanCode=\"SBX\" />
                      <LengthsOfStay> 
                        <LengthOfStay MinMaxMessageType=\"SetMinLOS\" Time=\"2\" /> 
                      </LengthsOfStay> 
                    </AvailStatusMessage>                    
                    <AvailStatusMessage BookingLimit=\"10\">
                      <StatusApplicationControl Start=\"2010-01-03\" End=\"2010-01-03\" InvTypeCode=\"504963\" RatePlanCode=\"SBX\" />
                      <RestrictionStatus Status=\"Open\" />
                    </AvailStatusMessage>
                    <AvailStatusMessage>
                      <StatusApplicationControl Start=\"2010-01-03\" End=\"2010-01-03\" InvTypeCode=\"504963\" RatePlanCode=\"SBX\" />
                      <LengthsOfStay> 
                        <LengthOfStay MinMaxMessageType=\"SetMinLOS\" Time=\"3\" /> 
                      </LengthsOfStay>
                    </AvailStatusMessage>                    
                   </AvailStatusMessages>
                </OTA_HotelAvailNotifRQ>
                </soap:Body>
                </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/siteminder', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Success/>', $response);
        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019371']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '504963']);
        $availabilities = $this->getRepository(Availability::class)->findByDateRange(
            $partner,
            new \DateTime('2010-01-03'),
            new \DateTime('2010-01-03'),
            [$product]
        );

        /** @var Availability $availability */
        foreach ($availabilities as $availability) {
            if ('01' === $availability->getDate()->format('d')) {
                $this->assertEquals(3, $availability->getStock()); // Availability keeps the same value
            } else if ('02' === $availability->getDate()->format('d')) {
                $this->assertEquals(8, $availability->getStock()); // Availability keeps the same value
            } else if ('03' === $availability->getDate()->format('d')) {
                $this->assertEquals(10, $availability->getStock()); // Availability keeps the same value
            }

            $this->assertEquals(false, $availability->isStopSale());
        }
    }

    public function testStopSaleRequest()
    {
        $this->testSiteminderHotelAvailNotifOperation();
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                  xmlns:soap=\"http://www.w3.org/2003/05/soap-envelope\"
                  xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                  xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                  <soap:Header>
                    <wss:Security soap:mustUnderstand = \"1\">
                      <wss:UsernameToken>
                        <wss:Username>siteminder</wss:Username>
                        <wss:Password>password</wss:Password>
                      </wss:UsernameToken>
                    </wss:Security>
                  </soap:Header>
                <soap:Body>
                <OTA_HotelAvailNotifRQ xmlns=\"http://www.opentravel.org/OTA/2003/05\" Version=\"1.0\" TimeStamp=\"2005-08-01T09:30:47+08:00\" EchoToken=\"abc123\">
                  <AvailStatusMessages HotelCode=\"00019371\">
                    <AvailStatusMessage>
                      <StatusApplicationControl Start=\"2010-01-03\" End=\"2010-01-03\" InvTypeCode=\"504963\" RatePlanCode=\"SBX\" />
                      <RestrictionStatus Status=\"Close\" />
                    </AvailStatusMessage>
                    <AvailStatusMessage>
                       <StatusApplicationControl End=\"2010-01-03\" InvTypeCode=\"504963\" RatePlanCode=\"SBX\" Start=\"2010-01-03\" />
                       <LengthsOfStay>
                          <LengthOfStay MinMaxMessageType=\"SetMinLOS\" Time=\"1\" />
                       </LengthsOfStay>
                    </AvailStatusMessage>
                   </AvailStatusMessages>
                </OTA_HotelAvailNotifRQ>
                </soap:Body>
                </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/siteminder', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Success/>', $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019371']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '504963']);
        $availabilities = $this->getRepository(Availability::class)->findByDateRange(
            $partner,
            new \DateTime('2010-01-03'),
            new \DateTime('2010-01-03'),
            [$product]
        );

        $this->assertEquals(1, count($availabilities));

        /** @var Availability $availability */
        foreach ($availabilities as $availability) {
            $this->assertEquals(10, $availability->getStock()); // Availability keeps the same value
            $this->assertEquals(true, $availability->isStopSale());
        }
    }

    public function testSiteminderHotelAvailOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <soap:Envelope
                    xmlns:soap = \"http://www.w3.org/2003/05/soap-envelope\"
                    xmlns:wss = \"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"
                    xmlns:ota = \"http://www.opentravel.org/OTA/2003/05\">
                        <soap:Header>
                            <wss:Security soap:mustUnderstand = \"1\">
                                <wss:UsernameToken>
                                    <wss:Username>siteminder</wss:Username>
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
                                            <ota:HotelRef HotelCode=\"00019371\"/>
                                        </ota:Criterion>
                                    </ota:HotelSearchCriteria>
                                </ota:AvailRequestSegment>
                            </ota:AvailRequestSegments>
                        </ota:OTA_HotelAvailRQ>
                    </soap:Body>
            </soap:Envelope>";


        $this->client->request('POST', '/api/ext/soap/ota/siteminder', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();


        $this->assertContains('<ns2:Success/>', $response);
        $this->assertContains('<ns2:RoomType RoomTypeCode="504963"><ns2:RoomDescription Name="Standard room 504963"/></ns2:RoomType>', $response);
        $this->assertContains('<ns2:RatePlan RatePlanCode="SBX"', $response);
        $this->assertContains('<ns2:RatePlanDescription Name="Smartbox Standard Rate"/>', $response);
        $this->assertContains('<ns2:RoomType RoomTypeCode="286201"><ns2:RoomDescription Name="Suite room 286201"/></ns2:RoomType>', $response);
    }

    public function testSiteminderHotelAvailOperationTwo()
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


        $this->client->request('POST', '/api/ext/soap/ota/siteminder', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns2:Success/>', $response);
    }

    public function testSiteminderPingOperation()
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


        $this->client->request('POST', '/api/ext/soap/ota/siteminder', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<ns1:Success/>', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
        $this->assertContains('<ns1:EchoData>' . $randomMessage . '</ns1:EchoData>', $response);
    }
}
