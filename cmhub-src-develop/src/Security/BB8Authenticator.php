<?php

namespace App\Security;

use App\Model\BB8ErrorCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class BB8Authenticator
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BB8Authenticator extends AbstractCmHubAuthenticator
{
    /**
     *
     * @param Request $request
     *
     * @return array|mixed|null
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
     * @return JsonResponse
     */
    protected function authenticationResponse(AuthenticationException $authException)
    {
        return new JsonResponse(
            [
                "code"  => "401",
                "error" => "Invalid credentials",
            ],
            BB8ErrorCode::AUTHENTICATION_ERROR
        );
    }
}
