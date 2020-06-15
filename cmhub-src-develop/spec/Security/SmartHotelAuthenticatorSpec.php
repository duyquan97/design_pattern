<?php

namespace spec\App\Security;

use App\Security\SmartHotelAuthenticator;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Twig\Environment;

class SmartHotelAuthenticatorSpec extends ObjectBehavior
{
    private const USERNAME = 'smarthotel';
    private const PASSWORD = 'password';

    function it_is_initializable()
    {
        $this->shouldHaveType(SmartHotelAuthenticator::class);
    }

    function let(UserPasswordEncoderInterface $encoder, Environment $templating, CmhubLogger $logger)
    {
        $this->beConstructedWith($encoder, $templating, $logger);
    }

    function it_gets_credentials_request(Request $request)
    {
        $request
            ->getContent()
            ->willReturn(
                '<?xml version="1.0" encoding="UTF-8"?>
	                <SomeOperation EchoToken="aaaaaa" PrimaryLangID="eng" Target="Production" TimeStamp="2018-07-29T07:38:54.729Z" Version="1.0" xmlns="http://www.opentravel.org/OTA/2003/05"> 
		                <POS>
			                <Source>
				                <RequestorID ID="smarthotel" MessagePassword="password"></RequestorID>
			                </Source>
		                </POS>
	                </SomeOperation>'
            );

        $this
            ->getCredentials($request)
            ->shouldBe(
                [
                    'username' => self::USERNAME,
                    'password' => self::PASSWORD
                ]
            );
    }

    function it_does_not_get_credentials_from_wrong_request(Request $request)
    {
        $request
            ->getContent()
            ->willReturn(
                '<?xml version="1.0" encoding="UTF-8"?>
	                <SomeOperation EchoToken="aaaaaa" PrimaryLangID="eng" Target="Production" TimeStamp="2018-07-29T07:38:54.729Z" Version="1.0" xmlns="http://www.opentravel.org/OTA/2003/05"> 
                        <Source>
                            <RequestorID ID="smarthotel" MessagePassword="password"></RequestorID>
                        </Source>
	                </SomeOperation>'
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
        $userProvider->loadUserByUsername(self::USERNAME)->willReturn($user);
        $this
            ->getUser(
                [
                    'username' => self::USERNAME,
                    'password' => self::PASSWORD
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
                    'username' => self::USERNAME,
                    'password' => self::PASSWORD
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
