<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class R2D2ControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function testUnauthorizedRequest()
    {
        $this->client->request(
            'GET',
            '/r2d2/availability/252039?start=2019-03-15',
            [],
            [],
            [
                'HTTP_CONTENT_TYPE' => 'application/json'
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testMissingEndDate()
    {
        $this->client->request(
            'GET',
            '/r2d2/availability/252039?start=2019-03-15',
            [],
            [],
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic cjJkMjpOQThtYVVkOVJRbng5RkhiYVRSRXBKZTN3dThGQ1RzcA==',
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertRegExp('/Bad Request/', $response->getContent());
    }

    public function testMissingStartDate()
    {
        $this->client->request(
            'GET',
            '/r2d2/availability/252039?end=2019-03-15',
            [],
            [],
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic cjJkMjpOQThtYVVkOVJRbng5RkhiYVRSRXBKZTN3dThGQ1RzcA==',
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertRegExp('/Bad Request/', $response->getContent());
    }

    public function testWrongDateFormat()
    {
        $this->client->request(
            'GET',
            '/r2d2/availability/252039?start=2019-03-15&end=2019',
            [],
            [],
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic cjJkMjpOQThtYVVkOVJRbng5RkhiYVRSRXBKZTN3dThGQ1RzcA==',
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertRegExp('/Bad Request/', $response->getContent());
    }

    public function testGetAvailabilities()
    {
        $this->client->request(
            'GET',
            '/r2d2/availability/252039?start=2019-03-16&end=2019-03-19',
            [],
            [],
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic cjJkMjpOQThtYVVkOVJRbng5RkhiYVRSRXBKZTN3dThGQ1RzcA==',
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('[{"date":"2019-03-16","quantity":20},{"date":"2019-03-17","quantity":20},{"date":"2019-03-18","quantity":20},{"date":"2019-03-19","quantity":20}]', $response->getContent());
    }

    public function testPriceMissingEndDate()
    {
        $this->client->request(
            'GET',
            '/r2d2/price/252039?start=2019-03-15',
            [],
            [],
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic cjJkMjpOQThtYVVkOVJRbng5RkhiYVRSRXBKZTN3dThGQ1RzcA==',
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertRegExp('/Bad Request/', $response->getContent());
    }

    public function testPriceMissingStartDate()
    {
        $this->client->request(
            'GET',
            '/r2d2/price/252039?end=2019-03-15',
            [],
            [],
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic cjJkMjpOQThtYVVkOVJRbng5RkhiYVRSRXBKZTN3dThGQ1RzcA==',
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertRegExp('/Bad Request/', $response->getContent());
    }

    public function testPriceWrongDateFormat()
    {
        $this->client->request(
            'GET',
            '/r2d2/price/252039?start=2019-03-15&end=2019',
            [],
            [],
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic cjJkMjpOQThtYVVkOVJRbng5RkhiYVRSRXBKZTN3dThGQ1RzcA==',
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertRegExp('/Bad Request/', $response->getContent());
    }

    public function testGetPrices()
    {
        $this->client->request(
            'GET',
            '/r2d2/price/252039?start=2019-03-10&end=2019-03-16',
            [],
            [],
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Basic cjJkMjpOQThtYVVkOVJRbng5RkhiYVRSRXBKZTN3dThGQ1RzcA==',
            ],
            ''
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('[{"date":"2019-03-15","quantity":199},{"date":"2019-03-16","quantity":109.9}]', $response->getContent());
    }
}
