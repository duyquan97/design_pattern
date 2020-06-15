<?php

namespace spec\App\Service\EAI;

use App\Entity\Transaction;
use App\Entity\TransactionStatus;
use App\Exception\EAIClientException;
use App\Service\EAI\EAIClient;
use App\Service\EAI\EAIResponse;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;

/**
 * Class EAIClientSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EAIClientSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EAIClient::class);
    }

    function let(
        Client $client,
        CmhubLogger $logger
    ) {
        $this->beConstructedWith($client, $logger, 'username', 'password');
    }

    function it_request(Client $client, ResponseInterface $response)
    {
        $response->getBody()->willReturn('body');
        $response->getHeaders()->willReturn([]);
        $response->getStatusCode()->willReturn(200);

        $path = 'eai_endpoint';
        $data = [];
        $client->request(
            'POST',
            Argument::type('string'),
            Argument::type('array')
        )->willReturn($response);

        $eaiResponse = $this->request($path, $data);
        $eaiResponse->shouldBeAnInstanceOf(EAIResponse::class);
        $eaiResponse->getResponse()->shouldBe('body');
        $eaiResponse->getStatusCode()->shouldBe(200);
        $eaiResponse->getHeaders()->shouldBe([]);
        $eaiResponse->getStatus()->shouldBe(TransactionStatus::SENT);
    }

    function it_throw_invalid_request_exception(Client $client, ResponseInterface $response)
    {
        $body = [
            'code' => 400,
            'message' => 'Bad Request:The body contains more elements than expected'
        ];
        $response->getBody()->willReturn(json_encode($body));
        $response->getHeaders()->willReturn([]);
        $response->getStatusCode()->willReturn(400);

        $path = 'eai_endpoint';
        $data = [];
        $client->request(
            'POST',
            Argument::type('string'),
            Argument::type('array')
        )->willReturn($response);

        $this->shouldThrow(EAIClientException::class)->during('request', [$path, $data]);
    }

    function it_throw_eai_client_exception(Client $client)
    {
        $path = 'eai_endpoint';
        $data = [];
        $client->request(
            'POST',
            Argument::type('string'),
            Argument::type('array')
        )->willThrow(new BadResponseException('Invalid Request', new Request('POST', $path)));

        $this->shouldThrow(EAIClientException::class)->during('request', [$path, $data]);
    }
}
