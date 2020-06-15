<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Exception\ValidationException;
use App\Service\ChannelManager\SoapOta\Operation\V2007B\PingOperation;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;

class PingOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PingOperation::class);
    }

    function let(CmhubLogger $logger)
    {
        $this->beConstructedWith($logger);
    }

    function it_throws_an_exception_if_there_is_no_message(\StdClass $request)
    {
        $request->EchoData = '';

        $this->shouldThrow(ValidationException::class)->during('handle', [$request]);
    }

    function it_handles_operation(\StdClass $request, CmhubLogger $logger)
    {
        $result = $request->EchoData = 'Pa que quieres saber eso, jaja saludos';
        $logger->addOperationInfo(LogAction::PING, null, $this)->shouldBeCalled();

        $this->handle($request)->shouldBe([
            'EchoData' => $result,
        ]);
    }
}
