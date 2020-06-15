<?php

namespace spec\App\Security;

use App\Model\BB8ErrorCode;
use App\Security\BB8Authenticator;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Twig\Environment;

class BB8AuthenticatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BB8Authenticator::class);
    }

    function let(UserPasswordEncoderInterface $encoder, Environment $templating, CmhubLogger $logger)
    {
        $this->beConstructedWith($encoder, $templating, $logger);
    }

    function it_error_while_get_credential(Request $request)
    {
        $this->shouldThrow(\Error::class)->during('getCredentials', [$request]);
    }

    function it_returns_empty_user_if_not_post(Request $request, ParameterBag $parameterBag)
    {
        $request->headers = $parameterBag;
        $parameterBag->get('PHP_AUTH_USER')->willReturn('');
        $parameterBag->get('PHP_AUTH_PW')->willReturn('');
        $this
            ->getCredentials($request)
            ->shouldBe(
                [
                    'username' => '',
                    'password' => ''
                ]
            );
    }

    function it_gets_credentials_from_headers_request(Request $request, ParameterBag $parameterBag)
    {
        $request->headers = $parameterBag;
        $parameterBag->get('PHP_AUTH_USER')->willReturn('smartboxbb8');
        $parameterBag->get('PHP_AUTH_PW')->willReturn('password');
        $this
            ->getCredentials($request)
            ->shouldBe(
                [
                    'username' => 'smartboxbb8',
                    'password' => 'password'
                ]
            );
    }

    function it_gets_user_from_given_credentials(UserProviderInterface $userProvider, UserInterface $user)
    {
        $userProvider->loadUserByUsername('smartboxbb8')->willReturn($user);
        $this
            ->getUser(
                [
                    'username' => 'smartboxbb8',
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
                    'username' => 'smartboxbb8',
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

    function it_does_support_request(Request $request)
    {
        $this->supports($request)->shouldBe(true);
    }

    function it_renders_failure_view_on_authentication_failure(Request $request, AuthenticationException $exception)
    {
        $this->onAuthenticationFailure($request, $exception)->shouldBeAnInstanceOf(Response::class);
    }

    function it_renders_failure_view_on_anonymous_request(Request $request, AuthenticationException $exception) {
        $this->start($request, $exception);
    }
}
