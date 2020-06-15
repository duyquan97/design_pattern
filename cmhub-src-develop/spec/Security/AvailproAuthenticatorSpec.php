<?php

namespace spec\App\Security;

use App\Security\AvailproAuthenticator;
use App\Service\ChannelManager\AvailPro\AvailProIntegration;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Twig\Environment;

class AvailproAuthenticatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailproAuthenticator::class);
    }

    function let(UserPasswordEncoderInterface $encoder, Environment $templating, CmhubLogger $logger)
    {
        $this->beConstructedWith($encoder, $templating, $logger);
    }

    function it_gets_credentials_from_get_parameters(Request $request)
    {
        $request->isMethod('POST')->willReturn(false);
        $request->get('login')->willReturn('username');
        $request->get('password')->willReturn('password');

        $this
            ->getCredentials($request)
            ->shouldBe(
                [
                    'username' => 'username',
                    'password' => 'password'
                ]
            );
    }

    function it_gets_credentials_from_xml_request(Request $request)
    {
        $request->isMethod('POST')->willReturn(true);
        $request
            ->getContent()
            ->willReturn(
                '<?xml version="1.0" encoding="utf-8"?>
                <message xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                    <authentication login="username" password="password" />
                    <inventoryUpdate hotelId="PAR00054714">
                        <room id="559571">
                            <inventory>
                                <availability from="2017-12-28" to="2017-12-28" quantity="1" />
                                <availability from="2017-12-29" to="2017-12-31" quantity="1" />
                                <availability from="2018-01-01" to="2018-01-01" quantity="1" />
                              <availability from="2018-01-02" to="2018-03-31" quantity="1" />
                              <availability from="2018-04-01" to="2018-12-30" quantity="1" />
                            </inventory>
                        </room>
                        <room id="559578">
                            <inventory>
                                <availability from="2017-12-28" to="2017-12-28" quantity="1" />
                                <availability from="2017-12-29" to="2017-12-31" quantity="1" />
                                <availability from="2018-01-01" to="2018-01-01" quantity="1" />
                              <availability from="2018-01-02" to="2018-03-31" quantity="1" />
                              <availability from="2018-04-01" to="2018-12-30" quantity="1" />
                            </inventory>
                            <rate currency="EUR" rateCode="SBX" rateName="Smartbox Standard Rate">
                                <planning from="2017-12-28" to="2017-12-28" minimumStay="1" maximumStay="1" unitPrice="210" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2017-12-29" to="2017-12-31" minimumStay="1" maximumStay="1" unitPrice="273" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-01-01" to="2018-01-01" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-01-02" to="2018-03-31" minimumStay="1" maximumStay="1" unitPrice="294" noArrival="false" noDeparture="false" isClosed="false" />
                                <planning from="2018-04-01" to="2018-12-30" minimumStay="1" maximumStay="1" unitPrice="340.0000" noArrival="false" noDeparture="false" isClosed="false" />
                            </rate>
                        </room>
                    </inventoryUpdate>
                </message>'
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

    function it_gets_user_from_given_credentials(UserProviderInterface $userProvider, UserInterface $user)
    {
        $userProvider->loadUserByUsername('username')->willReturn($user);
        $this
            ->getUser(
                [
                    'username' => 'username',
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
                    'username' => 'username',
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

    function it_renders_failure_view_on_authentication_failure(Environment $templating, Request $request, Response $response, AuthenticationException $exception)
    {
        $this->it_renders_failure_template($templating, $request, $response, $exception);
        $this->onAuthenticationFailure($request, $exception)->shouldBeAnInstanceOf(Response::class);
    }

    function it_renders_failure_view_on_anonymous_request(Environment $templating, Request $request, Response $response, AuthenticationException $exception) {
        $this->it_renders_failure_template($templating, $request, $response, $exception);
        $this->start($request, $exception);
    }

    private function it_renders_failure_template(Environment $templating, Request $request, Response $response, AuthenticationException $exception) {
        $exception->getMessageKey()->willReturn('message_key: message_data');
        $exception->getMessageData()->willReturn([
            'message_key' => 'the key',
            'message_data' => 'the_data'
        ]);

        $templating
            ->render(
                AvailProIntegration::FAILURE_TEMPLATE,
                [
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'the key: the_data'
                ]
            )
            ->willReturn('view_data');
}
}
