<?php

namespace App\Security;

use App\Model\WubookErrorCode;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class WubookAuthenticator
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WubookAuthenticator extends AbstractCmHubAuthenticator
{
    /**
     *
     * @param Request $request
     *
     * @return array|mixed|null
     */
    public function getCredentials(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent());

            if (isset($data->cm_auth)) {
                $credentials = $data->cm_auth;

                return [
                    'username' => isset($credentials->username) ? $credentials->username : '',
                    'password' => isset($credentials->password) ? $credentials->password : '',
                ];
            }
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
     * @return JsonResponse
     */
    protected function authenticationResponse(AuthenticationException $authException)
    {
        return new JsonResponse(
            [
                "code"  => "401",
                "error" => "Invalid credentials",
            ],
            WubookErrorCode::AUTHENTICATION_ERROR
        );
    }
}
