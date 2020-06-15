<?php

namespace spec\App\Controller;

use App\Controller\StandardController;
use App\Utils\SoapServer;
use PhpSpec\ObjectBehavior;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

class StandardControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(StandardController::class);
    }

    function let(SoapServer $soapServer, RouterInterface $router, Environment $engine)
    {
        $this->beConstructedWith($soapServer, $engine, $router, 'host');
    }

    function it_handles_ota_soap_server(Request $request, Response $response, SoapServer $soapServer)
    {
        $request->getContent()->willReturn('request_content');

        $soapServer
            ->getResponse('request_content')
            ->willReturn($soapResponse = 'soap_server_response');

        $response->setContent($soapResponse)->shouldBeCalled();

        $this->serverAction($request, $response)->shouldBe($response);
    }

    function it_renders_wsdl(ResponseHeaderBag $headerBag, Response $response, Environment $engine, RouterInterface $router)
    {
        $endpoint = 'host';
        $router
            ->generate('api_cm_standard_ota_wsdl')
            ->willReturn($route = '/the/route');

        $response->headers = $headerBag;
        $headerBag->set('Content-Type', 'application/wsdl+xml')->shouldBeCalled();

        $engine
            ->render(
                'Api/Ext/Soap/Ota/wsdl.wsdl.twig',
                [
                    'host' => $endpoint,
                    'path'     => $route,
                ]
            )
            ->willReturn('the_view');

        $response->setContent('the_view')->shouldBeCalled();
        $this->wsdlAction($response, $endpoint)->shouldBe($response);
    }
}
