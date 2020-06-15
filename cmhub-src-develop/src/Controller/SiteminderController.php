<?php

namespace App\Controller;

use App\Utils\SoapServer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SiteminderController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SiteminderController
{
    /**
     *
     * @var SoapServer
     */
    private $soapServer;

    /**
     *
     * @param SoapServer $siteminderSoapServer The soap server
     */
    public function __construct(SoapServer $siteminderSoapServer)
    {
        $this->soapServer = $siteminderSoapServer;
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
            $this->soapServer->getResponse(
                $request->getContent()
            )
        );

        return $response;
    }

    /**
     *
     * @param Response $response
     * @param string   $path
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
