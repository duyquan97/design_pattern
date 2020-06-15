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
class SoapAuthenticator extends AbstractCmHubAuthenticator
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

        $namespaces = $xml->getNamespaces(true);
        $soapNamespace = array_search('http://www.w3.org/2003/05/soap-envelope', $namespaces);
        if (!$soapNamespace) {
            $soapNamespace = array_search('http://schemas.xmlsoap.org/soap/envelope/', $namespaces);
        }
        $wsseNamespace = array_search('http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', $namespaces);
        $otaNamespace = array_search('http://www.opentravel.org/OTA/2003/05', $namespaces);

        if ('OTA_PingRQ' === $xml->children($soapNamespace, true)->Body->children($otaNamespace, true)->getName()) {
            return false;
        }

        $data = [
            'username' => '',
            'password' => '',
        ];

        $header = $xml->children($soapNamespace, true)->Header;
        if (!$header instanceof \SimpleXMLElement) {
            return $data;
        }

        $security = $header->children($wsseNamespace, true)->Security;
        if (!$security instanceof \SimpleXMLElement) {
            return $data;
        }

        try {
            return [
                'username' => (string) $security
                    ->children($wsseNamespace, true)->UsernameToken
                    ->children($wsseNamespace, true)->Username,
                'password' => (string) $security
                    ->children($wsseNamespace, true)->UsernameToken
                    ->children($wsseNamespace, true)->Password,
            ];
        } catch (\Exception $exception) {
            // TODO: Log exception

            return $data;
        }
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
