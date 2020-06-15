<?php

namespace spec\App\Security;

use App\Security\SoapAuthenticator;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Twig\Environment;

class SoapAuthenticatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(SoapAuthenticator::class);
    }

    function let(UserPasswordEncoderInterface $encoder, Environment $templating, CmhubLogger $logger)
    {
        $this->beConstructedWith($encoder, $templating, $logger);
    }

    function it_returns_null_on_authentication_success(Request $request, TokenInterface $token)
    {
        $this->onAuthenticationSuccess($request, $token, 'key')->shouldBe(null);
    }

    function it_does_not_support_remember_me()
    {
        $this->supportsRememberMe()->shouldBe(false);
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

    function it_gets_credentials_from_request(Request $request)
    {
        $request
            ->getContent()
            ->willReturn(
                '<?xml version="1.0" encoding="UTF-8"?>
                    <soap:Envelope
                    xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
                    xmlns:wss = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
                    xmlns:ota = "http://www.opentravel.org/OTA/2003/05">

                    <soap:Header>
                        <wss:Security soap:mustUnderstand = "1">
                            <wss:UsernameToken>
                                <wss:Username>username</wss:Username>
                                <wss:Password>password</wss:Password>
                            </wss:UsernameToken>
                        </wss:Security>
                    </soap:Header>

                    <soap:Body>
                    </soap:Body>

                    </soap:Envelope>'
            );

        $this
            ->getCredentials($request)
            ->shouldBe(
                [
                    'username' => 'username',
                    'password' => 'password'
                ]
            );
    }

    function it_does_not_get_credentials_from_wrong_request(Request $request)
    {
        $request
            ->getContent()
            ->willReturn(
                '<?xml version="1.0" encoding="UTF-8"?>
                    <soap:Envelope
                    xmlns:soap="http://www.w3.org/2003/05/soap-envelope"
                    xmlns:wss = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
                    xmlns:ota = "http://www.opentravel.org/OTA/2003/05">

                    <soap:Header>
                        <wss:Security soap:mustUnderstand = "1">
                            <wss:Token>
                                <wss:Username>username</wss:Username>
                                <wss:Password>password</wss:Password>
                            </wss:Token>
                        </wss:Security>
                    </soap:Header>

                    <soap:Body>
                    </soap:Body>

                    </soap:Envelope>'
            );

        $this
            ->getCredentials($request)
            ->shouldBe(
                [
                    'username' => '',
                    'password' => ''
                ]
            );
    }

    function it_gets_user_from_given_credentials(UserProviderInterface $userProvider, UserInterface $user)
    {
        $userProvider->loadUserByUsername('smarthotel')->willReturn($user);
        $this
            ->getUser(
                [
                    'username' => 'smarthotel',
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
                    'username' => 'smarthotel',
                    'password' => 'password'
                ],
                $user
            )
            ->shouldBe(true);
    }
}
