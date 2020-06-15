<?php

namespace App\Tests\Controller;

use App\Entity\Experience;
use App\Model\PartnerInterface;
use App\Tests\BaseWebTestCase;

/**
 * Class ProductControllerTest
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function testEaiUpdateExperience()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateProduct',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'type'           => 'experience',
                    'universe_id'    => 'STA',
                    'identifier'    => '123456789',
                    'name'           => 'My Experience',
                    'price'          => 100,
                    'commission'     => 5,
                    'commission_type' => 'percentage',
                    'partner_code'    => '00019091',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        /** @var PartnerInterface $partner */
        $experience = $this->getRepository(Experience::class)->findOneBy(['identifier' => '123456789']);
        $this->assertEquals('My Experience', $experience->getName());
        $this->assertEquals(100, $experience->getPrice());
        $this->assertEquals('00019091', $experience->getPartner()->getIdentifier());
    }

    public function testEaiCreateExperienceWithoutCommission()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateProduct',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'type'           => 'experience',
                    'universe_id'    => 'STA',
                    'identifier'    => '123456789',
                    'name'           => 'My Experience',
                    'price'          => 100,
                    'partner_code'    => '00019091',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        /** @var PartnerInterface $partner */
        $experience = $this->getRepository(Experience::class)->findOneBy(['identifier' => '123456789']);
        $this->assertEquals('My Experience', $experience->getName());
        $this->assertEquals(100, $experience->getPrice());
        $this->assertEquals('00019091', $experience->getPartner()->getIdentifier());
    }

    public function testEaiUpdateProduct()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateProduct',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'type'           => 'product',
                    'universe_id'    => 'STA',
                    'identifier'    => '123456789',
                    'name'           => 'My Experience',
                    'price'          => 100,
                    'commission'     => 5,
                    'commission_type' => 'percentage',
                    'partner_code'    => '00019091',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('skipped', $content['status']);
    }


    public function testEaiUpdateProductWithWrongUniverseId()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateProduct',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'type'           => 'experience',
                    'universe_id'    => 'STT',
                    'identifier'    => '123456789',
                    'name'           => 'My Experience',
                    'price'          => 100,
                    'commission'     => 5,
                    'commission_type' => 'percentage',
                    'partner_code'    => '00019091',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('skipped', $content['status']);
    }

    public function testEaiUpdateProductWithoutType()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateProduct',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'universe_id'    => 'STA',
                    'identifier'    => '123456789',
                    'name'           => 'My Experience',
                    'price'          => 100,
                    'commission'     => 5,
                    'commission_type' => 'percentage',
                    'partner_code'    => '00019091',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $content['status']);
    }

    public function testEaiUpdateProductWithoutUniverseId()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateProduct',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'type'           => 'product',
                    'identifier'    => '123456789',
                    'name'           => 'My Experience',
                    'price'          => 100,
                    'commission'     => 5,
                    'commission_type' => 'percentage',
                    'partner_code'    => '00019091',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('skipped', $content['status']);
    }

    public function testEaiUpdateProductWithWrongPartnerId()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateProduct',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'type'           => 'experience',
                    'universe_id'    => 'STA',
                    'identifier'    => '123456789',
                    'name'           => 'My Experience',
                    'price'          => 100,
                    'commission'     => 5,
                    'commissionType' => 'percentage',
                    'partner_code'    => '000190911111',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $content['status']);
    }

    public function testEaiUpdateProductPrice()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateProduct',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'identifier'    => '123456789',
                    'price'          => 100,
                    'commission'     => 5,
                    'commissionType' => 'percentage',
                ]
            )
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('success', $content['status']);
    }
}
