<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class SoapAuthenticator
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SmartHotelAuthenticator extends AbstractCmHubAuthenticator
{
    /**
     *
     * @param Request $request
     *
     * @return array|mixed|null
     */
    public function getCredentials(Request $request)
    {
        $xml = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);

        if (!$xml instanceof \SimpleXMLElement) {
            throw new BadRequestHttpException();
        }

        if (strpos($request->getContent(), 'OTA_Ping')) {
            return false;
        }

        $requestor = $xml->children(null, true)->children();
        if (isset($requestor->Source, $requestor->Source->RequestorID)) {
            $attributes = $requestor->Source->RequestorID->attributes();

            return [
                'username' => (string) $attributes->ID,
                'password' => (string) $attributes->MessagePassword,
            ];
        }

        return [
            'username' => '',
            'password' => '',
        ];
    }

    /**
     *
     * @param AuthenticationException $authException
     *
     * @return Response
     */
    protected function authenticationResponse(AuthenticationException $authException)
    {
        return null;
    }
}
