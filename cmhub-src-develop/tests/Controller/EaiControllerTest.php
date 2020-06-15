<?php

namespace App\Tests\Controller;

use App\Entity\Experience;
use App\Entity\Partner;
use App\Model\PartnerInterface;
use App\Service\ChannelManager\ChannelManagerList;
use App\Tests\BaseWebTestCase;

class EaiControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function testEaiUpdatePartnerHasPartnerLevelAuth()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdatePartner',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'id'                      => '00145577',
                    'channelManagerCode'          => 'wubook',
                    'displayName'             => 'Name',
                    'currencyCode'            => 'SEK',
                    'description'             => 'description',
                    'channelManagerHubApiKey' => 'whatever',
                    'isChannelManagerEnabled' => true,
                    'status'                  => 'partner',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        /** @var Partner $partner */
        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00145577']);
        $this->assertEquals('SEK', $partner->getCurrency());
        $this->assertEquals(ChannelManagerList::WUBOOK, $partner->getChannelManager()->getIdentifier());
        $this->assertNotNull($partner->getConnectedAt());
        $this->assertEquals('wubook', $partner->getUser()->getUsername());
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdatePartner',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'id'                      => '00127978',
                    'channelManagerCode'      => 'yieldplanet',
                    'displayName'             => 'Name',
                    'currencyCode'            => 'DEK',
                    'description'             => 'description',
                    'channelManagerHubApiKey' => 'sadsadsadsa',
                    'isChannelManagerEnabled' => true,
                    'status'                  => 'partner',
                ]
            )
        );
        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00127978']);
        $this->assertEquals(ChannelManagerList::YIELDPLANET, $partner->getChannelManager()->getIdentifier());
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('DEK', $partner->getCurrency());
        $this->assertNull($partner->getConnectedAt());
    }

    public function testEaiUpdatePartnerNoCMReceive()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdatePartner',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'id'                      => '00019091',
                    'channelManagerCode'      => null,
                    'displayName'             => 'Name',
                    'currencyCode'            => 'DEK',
                    'description'             => 'description',
                    'channelManagerHubApiKey' => 'sadsadsadsa',
                    'isChannelManagerEnabled' => true,
                    'status'                  => 'partner',
                ]
            )
        );
        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019091']);
        $this->assertEquals(null, $partner->getChannelManager());
        $this->assertEquals('DEK', $partner->getCurrency());
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertNull($partner->getConnectedAt());
    }

    public function testEaiUpdatePartnerHasCMLevelAuth()
    {
        /** @var Partner $partnerBeforeSend */
        $partnerBeforeSend = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019371']);
        $this->assertNotNull($partnerBeforeSend->getUser());
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdatePartner',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'id'                      => '00019371',
                    'channelManagerCode'      => 'siteminder',
                    'displayName'             => 'Name',
                    'currencyCode'            => 'SEK',
                    'description'             => 'description',
                    'channelManagerHubApiKey' => 'whatever',
                    'isChannelManagerEnabled' => true,
                    'status'                  => 'partner',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        /** @var Partner $partner */
        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019371']);
        $this->assertEquals('SEK', $partner->getCurrency());
        $this->assertNull($partner->getUser());
        $this->assertFalse($partner->getChannelManager()->hasPartnerLevelAuth());
        $this->assertNull($partner->getUser());
        $this->assertEquals(ChannelManagerList::SITEMINDER, $partner->getChannelManager()->getIdentifier());
        $this->assertNull($partner->getUser());
        $this->assertNull($partner->getConnectedAt());
    }

    public function testEaiCreatePartnerHasPartnerLevelAuth()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdatePartner',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'id'                      => '12342455632431',
                    'channelManagerCode'      => 'yieldplanet',
                    'displayName'             => 'Name',
                    'currencyCode'            => 'SEK',
                    'description'             => 'description',
                    'channelManagerHubApiKey' => 'whatever',
                    'isChannelManagerEnabled' => true,
                    'status'                  => 'partner',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        /** @var Partner $partner */
        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '12342455632431']);
        $this->assertEquals('SEK', $partner->getCurrency());
        $this->assertNotNull($partner->getUser());
        $this->assertTrue($partner->getChannelManager()->hasPartnerLevelAuth());
        $this->assertEquals(ChannelManagerList::YIELDPLANET, $partner->getChannelManager()->getIdentifier());
        $this->assertNull($partner->getConnectedAt());
    }

    public function testEaiUpdatePartnerWithoutChannelManagerCode()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdatePartner',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'id'                      => '00019091',
                    'displayName'             => 'Name',
                    'currencyCode'            => 'DEK',
                    'description'             => 'description',
                    'channelManagerHubApiKey' => 'sadsadsadsa',
                    'isChannelManagerEnabled' => true,
                    'status'                  => 'partner',
                ]
            )
        );

        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '00019091']);
        $this->assertEquals('DEK', $partner->getCurrency());
        $this->assertEquals(null, $partner->getChannelManager());
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testEaiUpdatePartnerWithoutIdAndCm()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdatePartner',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'id'                      => '001274545975',
                    'displayName'             => 'Name',
                    'currencyCode'            => 'DEK',
                    'description'             => 'description',
                    'channelManagerHubApiKey' => 'sadsadsadsa',
                    'isChannelManagerEnabled' => true,
                    'status'                  => 'partner',
                ]
            )
        );
        $partner = $this->getRepository(Partner::class)->findOneBy(['identifier' => '001274545975']);
        $this->assertEquals('DEK', $partner->getCurrency());
        $this->assertEquals(null, $partner->getChannelManager());
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

}
