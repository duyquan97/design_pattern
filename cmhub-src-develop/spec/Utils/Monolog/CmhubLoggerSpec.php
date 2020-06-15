<?php

namespace spec\App\Utils\Monolog;

use App\Utils\Monolog\CmhubLogger;
use App\Utils\Obfuscator;
use Monolog\Logger as MonologLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class CmhubLoggerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CmhubLogger::class);
    }

    function let(LoggerInterface $logger, TokenStorage $tokenStorage, RequestStack $requestStack,Obfuscator $obfuscate)
    {
        $this->beConstructedWith($logger, $tokenStorage, $requestStack,$obfuscate);
    }

    function it_adds_info_record(LoggerInterface $logger,Obfuscator $obfuscate)
    {
        $obfuscate->obfuscate(Argument::type('array'))->willReturn($context = ['pe' => 'pito']);
        $obfuscate->obfuscate('info')->willReturn('whatever');
        $logger->log(MonologLogger::INFO, "whatever", $context)->shouldBeCalled();

        $this->addRecord(MonologLogger::INFO, "info", [], $this);
    }
}
