<?php

namespace App\Controller;

use App\Utils\SoapifyTrait;
use App\Utils\SoapServer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TravelClickController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class TravelClickController
{
    use SoapifyTrait;

    /**
     *
     * @var SoapServer
     */
    private $soapServer;

    /**
     *
     * @param SoapServer $travelclickSoapServer The soap server
     */
    public function __construct(SoapServer $travelclickSoapServer)
    {
        $this->soapServer = $travelclickSoapServer;
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
                str_replace(
                    '<?xml version="1.0" encoding="UTF-8"?>',
                    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
                    $this->soapServer->getResponse(
                        $this->soapify(
                            preg_replace('/(\s)?standalone="yes"/i', '', $request->getContent())
                        )
                    )
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
     *
     * @throws NotFoundHttpException
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
