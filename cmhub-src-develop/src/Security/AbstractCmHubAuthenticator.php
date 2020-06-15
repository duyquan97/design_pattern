<?php

namespace App\Security;

use App\Utils\Monolog\CmhubLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Twig\Environment;

/**
 * Class AbstractCmHubAuthenticator
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
abstract class AbstractCmHubAuthenticator extends AbstractGuardAuthenticator
{
    /**
     *
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     *
     * @var Environment
     */
    protected $templating;

    /**
     * @var CmhubLogger
     */
    protected $logger;

    /**
     * //TODO: This contructor only should contain PasswordEncoder as is the only one used in the abstraction
     *
     * AbstractCmHubAuthenticator constructor.
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param Environment                  $templating
     * @param CmhubLogger                  $logger
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, Environment $templating, CmhubLogger $logger)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->templating = $templating;
        $this->logger = $logger;
    }

    /**
     *
     * @param Request $request
     *
     * @return array|mixed|null
     */
    abstract public function getCredentials(Request $request);

    /**
     *
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return null|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!$credentials['username']) {
            return null;
        }

        return $userProvider->loadUserByUsername($credentials['username']);
    }

    /**
     *
     * @param mixed         $credentials
     * @param UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return null|Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->authenticationResponse($exception);
    }

    /**
     *
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     *
     * @return null|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     *
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        return true;
    }

    /**
     *
     * @param Request                      $request
     * @param AuthenticationException|null $authException
     *
     * @return void
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return $this->authenticationResponse($authException);
    }

    /**
     *
     * @param AuthenticationException $authException
     *
     * @return Response
     */
    abstract protected function authenticationResponse(AuthenticationException $authException);
}
