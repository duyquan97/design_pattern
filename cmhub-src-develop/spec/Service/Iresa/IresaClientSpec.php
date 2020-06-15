<?php

namespace spec\App\Service\Iresa;

use App\Service\Iresa\IresaClient;
use App\Utils\Monolog\CmhubLogger;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IresaClientSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IresaClient::class);
    }

    function let(
        Client $client,
        CmhubLogger $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->beConstructedWith($client, 'username', 'password', $logger, $entityManager);
    }
//
//    function it_calls_endpoint_with_correct_data() {}
//
//    function it_throws_iresa_client_exception_if_exception_is_thrown() {}
//
//    function it_handles_failed_message() {}
}
