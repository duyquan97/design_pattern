<?php

namespace spec\App\EventListener;

use App\EventListener\RequestListener;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class RequestListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RequestListener::class);
    }

    function let(Stopwatch $stopwatch, CmhubLogger $logger)
    {
        $this->beConstructedWith($stopwatch, $logger);
    }

    function it_starts_stopwatch_and_logs_on_kernel_request(Request $request, RequestEvent $event, Stopwatch $stopwatch, CmhubLogger $logger)
    {
        $event->isMasterRequest()->willReturn(true);
        $event->getRequest()->willReturn($request);
        $request->get('_controller')->willReturn('controller_name');
        $stopwatch->start('controller_name')->shouldBeCalled();
        $request->getHost()->willReturn('host');
        $request->getContent()->willReturn('content');
        $request->getContentType()->willReturn('content_type');
        $request->getScheme()->willReturn('scheme');
        $request->getClientIp()->willReturn('client_ip');
        $request->getUri()->willReturn('uri');

        $this->onKernelRequest($event);
    }

    function it_stops_stopwatch_and_logs_execution_time(Response $response, ResponseHeaderBag $headerBag, StopwatchEvent $stopwatchEvent, Request $request, ResponseEvent $event, Stopwatch $stopwatch, CmhubLogger $logger)
    {
        $event->isMasterRequest()->willReturn(true);
        $event->getRequest()->willReturn($request);
        $event->getResponse()->willReturn($response);
        $response->getStatusCode()->willReturn(200);
        $response->getContent()->willReturn('response_body');
        $request->getHost()->willReturn('host');
        $request->getContent()->willReturn('content');
        $request->getContentType()->willReturn('content_type');
        $request->getScheme()->willReturn('scheme');
        $request->getClientIp()->willReturn('client_ip');
        $request->getUri()->willReturn('/api/ext/');
        $response->headers = $headerBag;
        $event->getResponse()->willReturn($response);
        $response->getContent()->willReturn('Response data');
        $headerBag->set('Content-Type', 'text/xml; charset=utf-8')->shouldBeCalled();
        $headerBag->set('Content-Length', strlen('Response data'))->shouldBeCalled();
        $request->get('_controller')->willReturn($name = 'controller_method');
        $stopwatch->isStarted($name)->willReturn(true);
        $stopwatch->stop($name)->shouldBeCalled()->willReturn($stopwatchEvent);
        $stopwatchEvent->getDuration()->willReturn(10);
        $stopwatchEvent->getMemory()->willReturn(100);
        $logger->addRecord(\Monolog\Logger::INFO, Argument::cetera(), Argument::type('array'), $this)->shouldBeCalled();
        $this->onKernelResponse($event);
    }

    function it_does_not_log_if_stopwatch_not_started(ResponseHeaderBag $headerBag, Response $response, Request $request, ResponseEvent $event, Stopwatch $stopwatch, CmhubLogger $logger)
    {
        $event->isMasterRequest(true);
        $event->getRequest()->willReturn($request);
        $event->isMasterRequest()->willReturn(true);
        $event->getResponse()->willReturn($response);
        $response->getContent()->willReturn('Response data');
        $event->getRequest()->willReturn($request);
        $request->getUri()->willReturn('/api/ext/');
        $response->headers = $headerBag;
        $headerBag->set('Content-Type', 'text/xml; charset=utf-8')->shouldBeCalled();
        $headerBag->set('Content-Length', strlen('Response data'))->shouldBeCalled();
        $request->get('_controller')->willReturn($name = 'controller_method');
        $stopwatch->isStarted($name)->willReturn(false);
        $logger->addRecord(\Monolog\Logger::INFO, Argument::cetera(), Argument::type('array'), $this)->shouldNotBeCalled();
        $this->onKernelResponse($event);
    }

    function it_does_not_log_if_is_not_master_request(ResponseEvent $event)
    {
        $event->isMasterRequest()->willReturn(false);
        $this->onKernelResponse($event)->shouldBe(null);
    }
}
