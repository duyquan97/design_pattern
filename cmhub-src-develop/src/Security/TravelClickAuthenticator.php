<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class TravelClickAuthenticator
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class TravelClickAuthenticator extends AbstractCmHubAuthenticator
{
    /**
     *
     * @param Request $request The request
     *
     * @return bool
     *
     * @throws BadRequestHttpException
     *
     */
    public function supports(Request $request): bool
    {
        $xml = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);

        if (!$xml instanceof \SimpleXMLElement) {
            throw new BadRequestHttpException();
        }

        if ('OTA_PingRQ' === $xml->getName()) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param Request $request
     *
     * @return string[]|mixed|null
     */
    public function getCredentials(Request $request)
    {
        try {
            return [
                'username' => (string) $request->headers->get('PHP_AUTH_USER'),
                'password' => (string) $request->headers->get('PHP_AUTH_PW'),
            ];
        } catch (\Exception $exception) {
            return [
                'username' => '',
                'password' => '',
            ];
        }
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
    protected function authenticationResponse(AuthenticationException $authException): Response
    {
        return new Response(
            $this->templating->render(
                'Api/Ext/Soap/TravelClick/Failure.xml.twig',
                [
                    'echoToken' => substr(md5(mt_rand()), 0, 12),
                    'timeStamp' => (new \DateTime())->format(\DateTime::ISO8601),
                    'code'      => Response::HTTP_UNAUTHORIZED,
                    'message'   => strtr($authException->getMessageKey(), $authException->getMessageData()),
                ]
            ),
            Response::HTTP_UNAUTHORIZED
        );
    }
}
