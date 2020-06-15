<?php

namespace spec\App\Security;

use App\Security\WubookAuthenticator;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Twig\Environment;

class WubookAuthenticatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(WubookAuthenticator::class);
    }

    function let(UserPasswordEncoderInterface $encoder, Environment $templating, CmhubLogger $logger)
    {
        $this->beConstructedWith($encoder, $templating, $logger);
    }

    function it_returns_empty_user_if_not_post(Request $request)
    {
        $request->isMethod('POST')->willReturn(false);
        $this
            ->getCredentials($request)
            ->shouldBe(
                [
                    'username' => '',
                    'password' => ''
                ]
            );
    }

    function it_gets_credentials_from_json_request(Request $request)
    {
        $request->isMethod('POST')->willReturn(true);
        $request
            ->getContent()
            ->willReturn(
                '{
                    "cm_auth" : { 
                        "username":"availpro", 
                        "password":"password"
                        },
                    "hotel_auth": {
                        "hotel_id": "00289058"
                        },
                    "action": "get_rates",
                    "data": { "data1" : "dataprovided1" }
                }'
            );

        $this
            ->getCredentials($request)
            ->shouldBe(
                [
                    'username' => 'availpro',
                    'password' => 'password'
                ]
            );
    }

    function it_gets_user_from_given_credentials(UserProviderInterface $userProvider, UserInterface $user)
    {
        $userProvider->loadUserByUsername('availpro')->willReturn($user);
        $this
            ->getUser(
                [
                    'username' => 'availpro',
                    'password' => 'password'
                ],
                $userProvider
            )
            ->shouldBe($user);
    }

    function it_returns_null_if_username_not_present(UserProviderInterface $userProvider)
    {
        $this
            ->getUser(
                [
                    'username' => null,
                ],
                $userProvider
            )
            ->shouldBe(null);
    }

    function it_returns_true_if_credentials_are_valid(UserInterface $user, UserPasswordEncoderInterface $encoder)
    {
        $encoder->isPasswordValid($user, 'password')->willReturn(true);
        $this
            ->checkCredentials(
                [
                    'username' => 'availpro',
                    'password' => 'password'
                ],
                $user
            )
            ->shouldBe(true);
    }

    function it_returns_false_if_credentials_not_valid(UserInterface $user, UserPasswordEncoderInterface $encoder)
    {
        $encoder->isPasswordValid($user, 'password')->willReturn(false);
        $this
            ->checkCredentials(
                [
                    'username' => 'username',
                    'password' => 'password'
                ],
                $user
            )
            ->shouldBe(false);
    }

    function it_returns_null_on_authentication_success(Request $request, TokenInterface $token)
    {
        $this->onAuthenticationSuccess($request, $token, 'key')->shouldBe(null);
    }

    function it_does_not_support_remember_me()
    {
        $this->supportsRememberMe()->shouldBe(false);
    }
}
