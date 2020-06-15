<?php

namespace App\Controller;

use App\Utils\SoapifyTrait;
use App\Utils\SoapServer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SmartHotelController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SmartHotelController
{
    use SoapifyTrait;

    /**
     *
     * @var SoapServer
     */
    private $soapServer;

    /**
     *
     * @param SoapServer $smarthotelSoapServer The soap server
     */
    public function __construct(SoapServer $smarthotelSoapServer)
    {
        $this->soapServer = $smarthotelSoapServer;
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
        $response->setContent(
            $this->desoapify(
                $this
                    ->soapServer
                    ->getResponse(
                        $this->soapify($request->getContent())
                    )
            )
        );

        return $response;
    }

    /**
     *
     * @param Response $response The response
     * @param string   $path     The path
     *
     * @return Response
     */
    public function wsdlAction(Response $response, string $path): Response
    {
        $file = __DIR__ . '/../../public/' . $path;

        if (file_exists($file)) {
            return $response->setContent(file_get_contents($file));
        }

        throw new NotFoundHttpException();
    }
}
