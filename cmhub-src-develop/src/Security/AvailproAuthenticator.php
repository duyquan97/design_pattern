<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class AvailproAuthenticator
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailproAuthenticator extends AbstractCmHubAuthenticator
{
    /**
     *
     * @param Request $request
     *
     * @return array|mixed|null
     */
    public function getCredentials(Request $request)
    {
        // Some routes contains credentials into xml raw request
        if ($request->isMethod('POST')) {
            $xml = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);
            $data = json_decode($json, true);
            $credentials = $data['authentication']['@attributes'];

            return [
                'username' => $credentials['login'],
                'password' => $credentials['password'],
            ];
        }

        return [
            'username' => $request->get('login'),
            'password' => $request->get('password'),
        ];
    }

    /**
     *
     * @param AuthenticationException $authException
     *
     * @return Response
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function authenticationResponse(AuthenticationException $authException)
    {
        return new Response(
            $this->templating->render(
                'Api/Ext/Xml/AvailPro/V1/Failure.xml.twig',
                [
                    'code'    => Response::HTTP_UNAUTHORIZED,
                    'message' => strtr($authException->getMessageKey(), $authException->getMessageData()),
                ]
            ),
            403
        );
    }
}
