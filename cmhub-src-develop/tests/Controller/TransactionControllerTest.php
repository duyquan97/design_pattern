<?php

namespace App\Tests\Controller;

use App\Entity\Transaction;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Tests\BaseWebTestCase;

/**
 * Class TransactionControllerTest
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class TransactionControllerTest extends BaseWebTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function testUpdateStatusAction()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateStatus',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'transaction_id'     => 'aaaaaaaaaaaa',
                    'status'             => 'success',
                    'statusCode'         => 200,
                    'response'           => '{ "data": "success" }',
                ]
            )
        );

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(),$response->getContent());

        $transaction = $this->getRepository(Transaction::class)->findOneBy(['transactionId' => 'aaaaaaaaaaaa']);
        $this->assertEquals('success', $transaction->getStatus());
        $this->assertEquals(200, $transaction->getStatusCode());
        $this->assertEquals('{ "data": "success" }', $transaction->getResponse());
    }

    public function testUpdateStatusActionWithLegacyBody()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateStatus',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'transaction_id'     => 'aaaaaaaaaaaa',
                    'status'             => 'success',
                ]
            )
        );

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(),$response->getContent());

        $transaction = $this->getRepository(Transaction::class)->findOneBy(['transactionId' => 'aaaaaaaaaaaa']);
        $this->assertEquals('success', $transaction->getStatus());
        $this->assertEquals(200, $transaction->getStatusCode());
        $this->assertEquals('{ "data": "success" }', $transaction->getResponse());
    }

    public function testUpdateStatusActionMissingArgument()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateStatus',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'transaction'     => 'aaaaaaaaaaaa',
                    'status'             => 'success',
                ]
            )
        );

        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('Bad Request', $response->getContent());
    }

    public function testUpdateStatusActionWrongStatus()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateStatus',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'transaction_id'     => 'aaaaaaaaaaaa',
                    'status'             => 'abcde',
                ]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('Bad Request', $response->getContent());
    }

    public function testUpdateStatusActionTransactionNotExist()
    {
        $this->client->request(
            'POST',
            '/api/int/rest/v1/UpdateStatus',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Basic ZWFpOjE3czhtc0xYS2tNVnkwbVAwQmxUMEJON2JSc01ORUVF',
                'CONTENT_TYPE'       => 'application/json',
            ],
            json_encode(
                [
                    'transaction_id'     => 'abc',
                    'status'             => 'success',
                ]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('Bad Request', $response->getContent());
    }
}
