<?php

namespace App\Controller;

use App\Utils\SoapServer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Error;

/**
 * Class StandardController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class StandardController
{
    /**
     *
     * @var SoapServer
     */
    private $smartboxSoapServer;

    /**
     * @var Environment
     */
    private $templating;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $applicationHost;

    /**
     * StandardController constructor.
     *
     * @param SoapServer      $smartboxSoapServer
     * @param Environment     $templating
     * @param RouterInterface $router
     * @param string          $applicationHost
     */
    public function __construct(SoapServer $smartboxSoapServer, Environment $templating, RouterInterface $router, string $applicationHost)
    {
        $this->smartboxSoapServer = $smartboxSoapServer;
        $this->templating = $templating;
        $this->router = $router;
        $this->applicationHost = $applicationHost;
    }

    /**
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function serverAction(Request $request, Response $response): Response
    {
        $response->setContent($this->smartboxSoapServer->getResponse($request->getContent()));

        return $response;
    }

    /**
     *
     * @param Response $response
     *
     * @return Response
     *
     * @throws Error\LoaderError
     * @throws Error\RuntimeError
     * @throws Error\SyntaxError
     */
    public function wsdlAction(Response $response): Response
    {
        $serverPath = $this->router->generate('api_cm_standard_ota_wsdl');

        $response->headers->set('Content-Type', 'application/wsdl+xml');
        $response->setContent(
            $this
                ->templating
                ->render(
                    'Api/Ext/Soap/Ota/wsdl.wsdl.twig',
                    [
                        'host' => $this->applicationHost,
                        'path' => $serverPath,
                    ]
                )
        );

        return $response;
    }
}
