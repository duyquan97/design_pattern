<?php

namespace spec\App\Service\ChannelManager\BB8;

use App\Exception\AccessDeniedException;
use App\Exception\BB8OperationNotFoundException;
use App\Service\ChannelManager\BB8\BB8Integration;
use App\Service\ChannelManager\BB8\Operation\BB8OperationInterface;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BB8IntegrationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BB8Integration::class);
    }

    function let(
        BB8OperationInterface $operation,
        CmhubLogger $logger,
        AuthorizationCheckerInterface $authorizationChecker
    )
    {
        $this->beConstructedWith(
            [$operation],
            $logger,
            $authorizationChecker
        );

    }
    function it_handles_operation(
        BB8OperationInterface $operation,
        Request $request
    )
    {
        $data = [
            "currencyCode" => "EUR",
            "date" => "2019-03-20",
            "price" => 900,
            "rateBandId" => "SBX",
            "roomId" => "123ABC",
            "rateBandCode" => "SBX",
            "externalPartnerId" => "123ABC",
            "externalRoomId" => "123ABC",
            "externalCreatedAt" => "2019-03-20T12:22:34.392Z",
            "externalUpdatedAt" => "2019-03-20T12:22:34.392Z"
        ];

        $operation->supports('get_price')->shouldBeCalled()->willReturn(true);
        $operation->handle(Argument::any())->shouldBeCalled()->willReturn($data);
        $this->handle($request, 'get_price')->shouldBe($data);
    }

    function it_throws_exception(
        BB8OperationInterface $operation,
        Request $request,
        CmhubLogger $logger
    )
    {
        $operation->supports("get_something")->shouldBeCalled()->willReturn(false);

        $operation->handle(Argument::any())->shouldNotBeCalled();
        $logger->addRecord(\Monolog\Logger::ALERT, "BB8 Operation get_something not found", Argument::type('array'), $this)->shouldBeCalled();
        $this->shouldThrow(BB8OperationNotFoundException::class)->during('handle', [$request, "get_something"]);
    }

}
