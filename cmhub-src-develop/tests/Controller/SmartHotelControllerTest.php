<?php

namespace App\Tests\Controller;

use App\Entity\Partner;
use App\Entity\Product;
use App\Service\HubEngine\CmHubBookingEngine;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

ini_set("soap.wsdl_cache_enabled", 0);

class SmartHotelControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function testSmartHotelWsdl()
    {
        $this->client->request('GET', '/api/ext/soap/ota/smarthotel', [], [], [], '');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testSmartHotelPingOperation()
    {
        $randomMessage = substr(md5(mt_rand()), 0, 12);
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <OTA_PingRQ EchoToken=\"Example123\" PrimaryLangID=\"eng\" Target=\"Production\" TimeStamp=\"2018-07-29T07:38:54.729Z\" Version=\"1.0\" xmlns=\"http://www.opentravel.org/OTA/2003/05\">
                    <EchoData>$randomMessage</EchoData>
                </OTA_PingRQ>";


        $this->client->request('POST', '/api/ext/soap/ota/smarthotel', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<Success/>', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
        $this->assertContains('<EchoData>' . $randomMessage . '</EchoData>', $response);
    }

    public function testSmartHotelDescriptiveInfoOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                 <OTA_HotelDescriptiveInfoRQ EchoToken=\"aaaaaa\" PrimaryLangID=\"eng\" Target=\"Production\" TimeStamp=\"2018-07-29T07:38:54.729Z\" Version=\"1.0\" xmlns=\"http://www.opentravel.org/OTA/2003/05\">
                  <POS>
                    <Source>
                      <RequestorID ID=\"smarthotel\" MessagePassword=\"password\"></RequestorID>
                    </Source>
                  </POS>
                  <HotelDescriptiveInfos>
                    <HotelDescriptiveInfo HotelCode=\"00019157\">
                      <FacilityInfo SendGuestRooms=\"True\" />
                    </HotelDescriptiveInfo>
                  </HotelDescriptiveInfos>
                </OTA_HotelDescriptiveInfoRQ>";

        $this->client->request('POST', '/api/ext/soap/ota/smarthotel', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<Success/>', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
        $this->assertContains('<GuestRoom Code="252039"><TypeRoom Name="Standard room 252039"/></GuestRoom>', $response);
        $this->assertContains('<GuestRoom Code="207902"><TypeRoom Name="Suite room 207902"/></GuestRoom>', $response);
    }

    public function testSmartHotelRatePlanOperation()
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                    <OTA_HotelRatePlanRQ EchoToken=\"aaaaaa\" PrimaryLangID=\"eng\" Target=\"Production\" TimeStamp=\"2018-07-29T07:38:54.729Z\" Version=\"1.0\" xmlns=\"http://www.opentravel.org/OTA/2003/05\"> 
                        <POS>
                            <Source>
                                <RequestorID ID=\"smarthotel\" MessagePassword=\"password\"></RequestorID>
                            </Source>
                        </POS>
		                <RatePlans>
			                <RatePlan>
				                <HotelRef HotelCode = \"00019157\"/>
			                </RatePlan>
		                </RatePlans>
	                </OTA_HotelRatePlanRQ>";

        $this->client->request('POST', '/api/ext/soap/ota/smarthotel', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<Success/>', $response);
        $this->assertContains('Version="1.0"', $response);
        $this->assertContains('TimeStamp="', $response);
        $this->assertContains('<RatePlans>', $response);
        $this->assertContains('<RatePlan RatePlanCode="SBX">', $response);
        $this->assertContains('<SellableProducts>', $response);
        $this->assertContains('<SellableProduct InvTypeCode="252039"/>', $response);
        $this->assertContains('<SellableProduct InvTypeCode="207902"/>', $response);

    }

    public function testSmartHotelHotelRateAmountNotifOperation()
    {
        $randomAmount = intval(rand(1000, 9999));
        $randomAmount2 = intval(rand(1000, 9999));
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                        <OTA_HotelRateAmountNotifRQ EchoToken=\"Example123\" PrimaryLangID=\"eng\" Target=\"Production\" TimeStamp=\"2018-07-29T07:38:54.729Z\" Version=\"1.0\" xmlns=\"http://www.opentravel.org/OTA/2003/05\">
                            <POS>
                                <Source>
                                    <RequestorID ID=\"smarthotel\" MessagePassword=\"password\"></RequestorID>
                                </Source>
                            </POS>
                            <RateAmountMessages HotelCode=\"00019157\">
                                <RateAmountMessage>
                                    <StatusApplicationControl InvTypeCode=\"252039\" 
                RatePlanCode=\"1\" RateTier=\"1\" Start=\"2019-07-24\" End=\"2019-07-31\" Mon=\"true\" Fri=\"true\" />
                                    <Rates>
                                        <Rate>
                                            <BaseByGuestAmts>
                                                <BaseByGuestAmt NumberOfGuests=\"1\" AgeQualifyingCode=\"10\" AmountAfterTax=\"$randomAmount\" DecimalPlaces=\"2\" />
                                            </BaseByGuestAmts>
                                        </Rate>
                                    </Rates>
                                </RateAmountMessage>
                                <RateAmountMessage>
                                    <StatusApplicationControl InvTypeCode=\"207902\" 
                RatePlanCode=\"1\" RateTier=\"1\" Start=\"2019-07-24\" End=\"2019-07-31\" Mon=\"true\" Fri=\"true\" />
                                    <Rates>
                                        <Rate>
                                            <BaseByGuestAmts>
                                                <BaseByGuestAmt NumberOfGuests=\"1\" AgeQualifyingCode=\"10\" AmountAfterTax=\"$randomAmount2\" DecimalPlaces=\"2\" />
                                            </BaseByGuestAmts>
                                            <AdditionalGuestAmounts>
                                                <AdditionalGuestAmount Amount=\"1000\" DecimalPlaces=\"2\" />
                                            </AdditionalGuestAmounts>
                                        </Rate>
                                    </Rates>
                                </RateAmountMessage>
                            </RateAmountMessages>
                        </OTA_HotelRateAmountNotifRQ>";


        $this->client->request('POST', '/api/ext/soap/ota/smarthotel', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<Success/>', $response);
        $this->assertContains('version="1.0"', $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019157']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '252039']);

        $rates = $this->getContainer()->get(CmHubBookingEngine::class)->getRates($partner, new \DateTime('2019-07-24'), new \DateTime('2019-07-31'), [$product]);
        $this->assertGreaterThan(0, sizeof($rates->getProductRates()));
        foreach ($rates->getProductRates() as $productRate) {
            foreach ($productRate->getRates() as $rate) {
                if (in_array($rate->getStart()->format('w'), [
                    '1',
                    '5'
                ])) {
                    $this->assertEquals(($randomAmount / 100), $rate->getAmount());
                }
            }
        }

        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '207902']);

        $rates = $this->getContainer()->get(CmHubBookingEngine::class)->getRates($partner, new \DateTime('2019-07-24'), new \DateTime('2019-07-31'), [$product]);
        $this->assertGreaterThan(0, sizeof($rates->getProductRates()));
        foreach ($rates->getProductRates() as $productRate) {
            foreach ($productRate->getRates() as $rate) {
                if (in_array($rate->getStart()->format('w'), [
                    '1',
                    '5'
                ])) {
                    $this->assertEquals(($randomAmount2 / 100), $rate->getAmount());
                }
            }
        }
    }


    public function testSmartHotelHotelInvCountNotifOperation()
    {
        $randomStock = rand(10, 30);
        $randomStock2 = rand(10, 30);
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <OTA_HotelInvCountNotifRQ EchoToken=\"Example123\" PrimaryLangID=\"eng\" Target=\"Production\" TimeStamp=\"2018-07-29T07:38:54.729Z\" Version=\"1.0\" xmlns=\"http://www.opentravel.org/OTA/2003/05\"> 
                <POS>
                  <Source>
                    <RequestorID ID=\"smarthotel\" MessagePassword=\"password\"></RequestorID>
                  </Source>
                </POS>
                  <Inventories HotelCode=\"00019157\">
                    <Inventory>
                      <StatusApplicationControl InvTypeCode=\"252039\" Start=\"2018-07-24\" End=\"2018-07-31\" Mon=\"true\" Fri=\"true\"/>
                      <InvCounts>
                        <InvCount Count=\"$randomStock\" CountType=\"1\"/>
                      </InvCounts>
                    </Inventory>
                    <Inventory>
                      <StatusApplicationControl InvTypeCode=\"207902\" Start=\"2018-07-24\" End=\"2018-07-30\" Mon=\"true\" Fri=\"true\"/>
                      <InvCounts>
                        <InvCount Count=\"$randomStock2\" CountType=\"1\"/>
                      </InvCounts>
                    </Inventory>
                    <Inventory>
                      <StatusApplicationControl InvTypeCode=\"252039\" Start=\"2018-12-12\" End=\"2018-12-30\" />
                      <InvCounts>
                        <InvCount Count=\"11\" CountType=\"1\"/>
                      </InvCounts>
                    </Inventory>
                  </Inventories>
                </OTA_HotelInvCountNotifRQ>";


        $this->client->request('POST', '/api/ext/soap/ota/smarthotel', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->assertContains('<Success/>', $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019157']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '252039']);
        $product1 = $this->getRepository(Product::class)->findOneBy(['identifier' => '207902']);
        $availabilities = $this->getContainer()->get(CmHubBookingEngine::class)->getAvailabilities($partner, new \DateTime('2018-07-24'), new \DateTime('2018-07-31'), [$product]);
        $this->assertGreaterThan(0, sizeof($availabilities->getProductAvailabilities()));
        foreach ($availabilities->getProductAvailabilities() as $productAvailability) {
            foreach ($productAvailability->getAvailabilities() as $availability) {
                if (in_array($availability->getDate()->format('w'), [
                    '1',
                    '5'
                ])) {
                    $this->assertEquals($randomStock, $availability->getStock());
                } else {
                    $this->assertEquals(0, $availability->getStock());
                }
            }
        }

        $availabilities = $this->getContainer()->get(CmHubBookingEngine::class)->getAvailabilities($partner, new \DateTime('2018-07-24'), new \DateTime('2018-07-30'), [$product1]);
        $this->assertGreaterThan(0, sizeof($availabilities->getProductAvailabilities()));
        foreach ($availabilities->getProductAvailabilities() as $productAvailability) {
            foreach ($productAvailability->getAvailabilities() as $availability) {
                if (in_array($availability->getDate()->format('w'), [
                    '1',
                    '5'
                ])) {
                    $this->assertEquals($randomStock2, $availability->getStock());
                } else {
                    $this->assertEquals(0, $availability->getStock());
                }
            }
        }
    }

    public function testHotelBookingRuleNotifCloseOperation()
    {
        $this->testSmartHotelHotelInvCountNotifOperation();
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <OTA_HotelBookingRuleNotifRQ EchoToken=\"Example123\" PrimaryLangID=\"eng\" Target=\"Production\" TimeStamp=\"2018-07-29T07:38:54.729Z\" Version=\"1.0\" xmlns=\"http://www.opentravel.org/OTA/2003/05\">
                    <POS>
                        <Source>
                            <RequestorID ID=\"smarthotel\" MessagePassword=\"password\"></RequestorID>
                        </Source>
                    </POS>
                    <RuleMessages HotelCode=\"00019157\">
                        <RuleMessage>
                            <StatusApplicationControl InvTypeCode=\"252039\" RatePlanCode=\"1\" RateTier=\"1\" Start=\"2018-12-12\" End=\"2018-12-30\" Mon=\"true\" Weds=\"true\" Fri=\"true\" />
                            <BookingRules>
                                <BookingRule>
                                    <RestrictionStatus Restriction=\"Master\" Status=\"Close\" />
                                </BookingRule>
                            </BookingRules>
                        </RuleMessage>
                    </RuleMessages>
                </OTA_HotelBookingRuleNotifRQ>";


        $this->client->request('POST', '/api/ext/soap/ota/smarthotel', [], [], [], $xml);

        // Update availability again to see it keeps stop sell value
        $response = $this->client->getResponse()->getContent();
        $this->testSmartHotelHotelInvCountNotifOperation();

        $this->assertContains('<Success/>', $response);
        $this->assertContains('version="1.0"', $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019157']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '252039']);

        $availabilities = $this->getContainer()->get(CmHubBookingEngine::class)->getAvailabilities($partner, new \DateTime('2018-12-12'), new \DateTime('2018-12-30'), [$product]);
        $this->assertGreaterThan(0, sizeof($availabilities->getProductAvailabilities()));
        foreach ($availabilities->getProductAvailabilities() as $productAvailability) {
            $this->assertGreaterThan(0, $productAvailability->getAvailabilities());
            foreach ($productAvailability->getAvailabilities() as $availability) {
                if (in_array($availability->getDate()->format('w'), [
                    '1',
                    '3',
                    '5'
                ])) {
                    $this->assertEquals(true, $availability->isStopSale());
                    $this->assertEquals(11, $availability->getStock());
                } else {
                    $this->assertEquals(false, $availability->isStopSale());
                    $this->assertEquals(11, $availability->getStock());
                }
            }
        }
    }

    public function testHotelBookingRuleNotifCloseToArrivalOperation()
    {
        $this->testSmartHotelHotelInvCountNotifOperation();
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <OTA_HotelBookingRuleNotifRQ EchoToken=\"Example123\" PrimaryLangID=\"eng\" Target=\"Production\" TimeStamp=\"2018-07-29T07:38:54.729Z\" Version=\"1.0\" xmlns=\"http://www.opentravel.org/OTA/2003/05\">
                    <POS>
                        <Source>
                            <RequestorID ID=\"smarthotel\" MessagePassword=\"password\"></RequestorID>
                        </Source>
                    </POS>
                    <RuleMessages HotelCode=\"00019157\">
                        <RuleMessage>
                            <StatusApplicationControl InvTypeCode=\"252039\" RatePlanCode=\"1\" RateTier=\"1\" Start=\"2018-12-12\" End=\"2018-12-30\" Mon=\"true\" Weds=\"true\" Fri=\"true\" />
                            <BookingRules>
                                <BookingRule>
                                    <RestrictionStatus Restriction=\"Arrival\" Status=\"Close\" />
                                </BookingRule>
                            </BookingRules>
                        </RuleMessage>
                    </RuleMessages>
                </OTA_HotelBookingRuleNotifRQ>";


        $this->client->request('POST', '/api/ext/soap/ota/smarthotel', [], [], [], $xml);

        // Update availability again to see it keeps stop sell value
        $response = $this->client->getResponse()->getContent();
        $this->testSmartHotelHotelInvCountNotifOperation();

        $this->assertContains('<Success/>', $response);
        $this->assertContains('version="1.0"', $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019157']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '252039']);

        $availabilities = $this->getContainer()->get(CmHubBookingEngine::class)->getAvailabilities($partner, new \DateTime('2018-12-12'), new \DateTime('2018-12-30'), [$product]);
        $this->assertGreaterThan(0, sizeof($availabilities->getProductAvailabilities()));
        foreach ($availabilities->getProductAvailabilities() as $productAvailability) {
            $this->assertGreaterThan(0, $productAvailability->getAvailabilities());
            foreach ($productAvailability->getAvailabilities() as $availability) {
                if (in_array($availability->getDate()->format('w'), [
                    '1',
                    '3',
                    '5'
                ])) {
                    $this->assertEquals(true, $availability->isStopSale());
                    $this->assertEquals(11, $availability->getStock());
                } else {
                    $this->assertEquals(false, $availability->isStopSale());
                    $this->assertEquals(11, $availability->getStock());
                }
            }
        }
    }

    public function testHotelBookingRuleNotifOpenOperation()
    {
        $this->testSmartHotelHotelInvCountNotifOperation();
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <OTA_HotelBookingRuleNotifRQ EchoToken=\"Example123\" PrimaryLangID=\"eng\" Target=\"Production\" TimeStamp=\"2018-07-29T07:38:54.729Z\" Version=\"1.0\" xmlns=\"http://www.opentravel.org/OTA/2003/05\">
                    <POS>
                        <Source>
                            <RequestorID ID=\"smarthotel\" MessagePassword=\"password\"></RequestorID>
                        </Source>
                    </POS>
                    <RuleMessages HotelCode=\"00019157\">
                        <RuleMessage>
                            <StatusApplicationControl InvTypeCode=\"252039\" RatePlanCode=\"1\" RateTier=\"1\" Start=\"2018-12-12\" End=\"2018-12-30\" Mon=\"true\" Tue=\"true\" Weds=\"true\" Thur=\"true\" Fri=\"true\" Sat=\"true\" Sun=\"true\" />
                            <BookingRules>
                                <BookingRule>
                                    <RestrictionStatus Restriction=\"Master\" Status=\"Open\" />
                                </BookingRule>
                            </BookingRules>
                        </RuleMessage>
                    </RuleMessages>
                </OTA_HotelBookingRuleNotifRQ>";


        $this->client->request('POST', '/api/ext/soap/ota/smarthotel', [], [], [], $xml);
        $response = $this->client->getResponse()->getContent();

        $this->testSmartHotelHotelInvCountNotifOperation();
        $this->assertContains('<Success/>', $response);
        $this->assertContains('version="1.0"', $response);

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019157']);
        $product = $this->getRepository(Product::class)->findOneBy(['identifier' => '252039']);

        $availabilities = $this->getContainer()->get(CmHubBookingEngine::class)->getAvailabilities($partner, new \DateTime('2019-03-15'), new \DateTime('2019-03-20'), [$product]);
        $this->assertGreaterThan(0, sizeof($availabilities->getProductAvailabilities()));
        foreach ($availabilities->getProductAvailabilities() as $productAvailability) {
            foreach ($productAvailability->getAvailabilities() as $availability) {
                $this->assertEquals(false, $availability->isStopSale());
            }
        }
    }
}
