<?php

namespace spec\App\EventListener;

use App\EventListener\ExceptionListener;
use App\Exception\CmHubException;
use App\Exception\DateFormatException;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class ExceptionListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ExceptionListener::class);
    }

    function let(CmhubLogger $logger, Environment $engine)
    {
        $this->beConstructedWith($logger, $engine, 'prod');
    }

    function it_logs_exception_and_sets_xml_response_if_xml_request(Request $request, GetResponseForExceptionEvent $event, CmhubLogger $logger, Environment $engine)
    {
        $exception = new \Exception('error message', 500);
        $event->getException()->willReturn($exception);
        $event->getRequest()->willReturn($request);
        $request->getContent()->willReturn('request content');
        $logger->addRecord(
            \Monolog\Logger::INFO,
            'error message', [
            'type'    => 'exception',
            'ex_type' => 'unknown',
            'request' => 'request content',
            'message' => 'error message',
        ], $this)->shouldBeCalled();

        $request->getContentType()->willReturn('xml');
        $engine
            ->render(
                'Api/Ext/Soap/Ota/Failure.xml.twig',
                [
                    'fault_code'    => 500,
                    'message' => 'Internal Server Error. Please contact administrator',
                    'error_code' => 'Internal Server Error'
                ])
            ->shouldBeCalled();

        $event->setResponse(Argument::type(Response::class))->shouldBeCalled();
        $this->onKernelException($event);
    }

    function it_logs_exception_and_sets_xml_response_if_xml_not_found_request(Request $request, GetResponseForExceptionEvent $event, CmhubLogger $logger, Environment $engine)
    {
        $exception = new NotFoundHttpException('not found');
        $event->getException()->willReturn($exception);
        $event->getRequest()->willReturn($request);
        $request->getContent()->willReturn('request content');
        $logger->addRecord(
            \Monolog\Logger::INFO,
            'not found', [
            'type'    => 'exception',
            'ex_type' => 'unknown',
            'request' => 'request content',
            'message' => 'not found',
        ], $this)->shouldBeCalled();

        $request->getContentType()->willReturn('xml');
        $engine
            ->render(
                'Api/Ext/Soap/Ota/Failure.xml.twig',
                [
                    'fault_code'    => 404,
                    'message' => 'The url you are trying to access does not exists',
                    'error_code' => 'Not Found'
                ])
            ->shouldBeCalled();

        $event->setResponse(Argument::type(Response::class))->shouldBeCalled();
        $this->onKernelException($event);
    }

    function it_logs_exception_if_not_xml_request(Request $request, \Throwable $exception, GetResponseForExceptionEvent $event, CmhubLogger $logger)
    {
        $exception = new \Exception('error message', 500);
        $event->getException()->willReturn($exception);
        $event->getRequest()->willReturn($request);
        $request->getContent()->willReturn('request content');
        $request->getContentType()->willReturn('application/json');
        $logger->addRecord(
            \Monolog\Logger::INFO,
            'error message', [
            'type'    => 'exception',
            'ex_type' => 'unknown',
            'request' => 'request content',
            'message' => 'error message',
        ], $this)->shouldBeCalled();

        $this->onKernelException($event);
    }
}
