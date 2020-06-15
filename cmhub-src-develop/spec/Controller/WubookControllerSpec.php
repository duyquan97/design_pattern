<?php

namespace spec\App\Controller;

use App\Controller\WubookController;
use App\Entity\ChannelManager;
use App\Entity\CmUser;
use App\Exception\CmHubException;
use App\Exception\IresaClientException;
use App\Service\ChannelManager\Wubook\WubookIntegration;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class WubookControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(WubookController::class);
    }

    function let(WubookIntegration $wubookIntegration, CmhubLogger $logger)
    {
        $this->beConstructedWith($wubookIntegration, $logger, 'test');
    }

    function it_gets_rates(JsonResponse $jsonResponse, Request $request, WubookIntegration $wubookIntegration, TokenStorageInterface $tokenStorage, TokenInterface $token, CmUser $cmUser)
    {
        $json = [
            'cm_auth' => [
                'username' => 'availpro',
                'password' => 'password',
            ],
            'hotel_auth' => [
                'hotel_id' => '00289058',
            ],
            'action' => 'get_rates',
            'data' => [
                'data1' => 'dataprovided1',
            ],
        ];

        $jsonString = json_encode($json);
        $request->getContent()
            ->willReturn($jsonString);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($cmUser);

        $wubookIntegration->handle(json_decode($jsonString))->shouldBeCalled()->willReturn('hgjug');
        $response = [
            'code' => 200,
            'data' => 'hgjug',
        ];

        $jsonResponse->setData($response)->shouldBeCalled()->willReturn($jsonResponse);

        $this->indexAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }

    function it_returns_exception(JsonResponse $jsonResponse, Request $request, WubookIntegration $wubookIntegration, TokenInterface $token, CmUser $cmUser, TokenStorageInterface $tokenStorage, ChannelManager $channelManager)
    {
        $json = [
            'cm_auth' => [
                'username' => 'availpro',
                'password' => 'password',
            ],
            'hotel_auth' => [
                'hotel_id' => '00289058',
            ],
            'action' => 'get_rates',
            'data' => [],
        ];

        $jsonString = json_encode($json);
        $request->getContent()
            ->willReturn($jsonString);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($cmUser);

        $cmHubException = (new CmHubException())
            ->setCode(Response::HTTP_BAD_REQUEST)
            ->setMessage('Access Denied');
        $wubookIntegration->handle(json_decode($jsonString))->shouldBeCalled()->willThrow($cmHubException);
        $response = [
            'code' => Response::HTTP_BAD_REQUEST,
            'error' => 'Access Denied',
        ];

        $cmUser->getUsername()->willReturn($cmUser);
        $cmUser->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('availpro');

        $jsonResponse->setData($response)->shouldBeCalled()->willReturn($jsonResponse);
        $jsonResponse->setStatusCode(Response::HTTP_BAD_REQUEST)->shouldBeCalled()->willReturn($jsonResponse);

        $this->indexAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }

    function it_returns_iresa_exception_test(JsonResponse $jsonResponse, Request $request, WubookIntegration $wubookIntegration, ContainerInterface $container, TokenInterface $token, CmUser $cmUser, TokenStorageInterface $tokenStorage, ChannelManager $channelManager)
    {
        $json = [
            'cm_auth' => [
                'username' => 'availpro',
                'password' => 'password',
            ],
            'hotel_auth' => [
                'hotel_id' => '00289058',
            ],
            'action' => 'get_rates',
            'data' => [
            ],
        ];

        $response = [
            'code' => Response::HTTP_BAD_REQUEST,
            'error' => 'iResa error message',
        ];

        $jsonString = json_encode($json);
        $request->getContent()
            ->willReturn($jsonString);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($cmUser);

        $iresaClientException = (new IresaClientException('Access Denied', Response::HTTP_BAD_REQUEST, 'iResa error message'));
        $wubookIntegration->handle(json_decode($jsonString))->shouldBeCalled()->willThrow($iresaClientException);

        $cmUser->getUsername()->willReturn($cmUser);
        $cmUser->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('availpro');

        $jsonResponse->setData($response)->shouldBeCalled()->willReturn($jsonResponse);
        $jsonResponse->setStatusCode(Response::HTTP_BAD_REQUEST)->shouldBeCalled()->willReturn($jsonResponse);
        $container->getParameter('kernel.environment')->willReturn('test');

        $this->indexAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }

    function it_returns_iresa_exception_prod(JsonResponse $jsonResponse, Request $request, WubookIntegration $wubookIntegration, ContainerInterface $container, TokenInterface $token, CmUser $cmUser, TokenStorageInterface $tokenStorage, ChannelManager $channelManager)
    {
        $json = [
            'cm_auth' => [
                'username' => 'availpro',
                'password' => 'password',
            ],
            'hotel_auth' => [
                'hotel_id' => '00289058',
            ],
            'action' => 'get_rates',
            'data' => [
            ],
        ];

        $response = [
            'code' => Response::HTTP_BAD_REQUEST,
            'error' => 'Access Denied',
        ];

        $jsonString = json_encode($json);
        $request->getContent()
            ->willReturn($jsonString);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($cmUser);

        $iresaClientException = (new IresaClientException('Access Denied', Response::HTTP_BAD_REQUEST, 'Access Denied'));
        $wubookIntegration->handle(json_decode($jsonString))->shouldBeCalled()->willThrow($iresaClientException);

        $cmUser->getUsername()->willReturn($cmUser);
        $cmUser->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('availpro');

        $jsonResponse->setData($response)->shouldBeCalled()->willReturn($jsonResponse);
        $jsonResponse->setStatusCode(Response::HTTP_BAD_REQUEST)->shouldBeCalled()->willReturn($jsonResponse);
        $container->getParameter('kernel.environment')->willReturn('prod');

        $this->indexAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }

    function it_returns_general_exception(JsonResponse $jsonResponse, Request $request, WubookIntegration $wubookIntegration, TokenInterface $token, CmUser $cmUser, TokenStorageInterface $tokenStorage, ChannelManager $channelManager)
    {
        $json = [
            'cm_auth' => [
                'username' => 'availpro',
                'password' => 'password',
            ],
            'hotel_auth' => [
                'hotel_id' => '00289058',
            ],
            'action' => 'get_rates',
            'data' => [],
        ];

        $response = [
            'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'error' => 'Unexpected internal error. Please contact administrator.',
        ];

        $jsonString = json_encode($json);
        $request->getContent()
            ->willReturn($jsonString);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($cmUser);

        $exception = (new \Exception('Unexpected internal error. Please contact administrator.', Response::HTTP_INTERNAL_SERVER_ERROR));
        $wubookIntegration->handle(json_decode($jsonString))->shouldBeCalled()->willThrow($exception);

        $cmUser->getUsername()->willReturn($cmUser);
        $cmUser->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('availpro');

        $jsonResponse->setData($response)->shouldBeCalled()->willReturn($jsonResponse);
        $jsonResponse->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)->shouldBeCalled()->willReturn($jsonResponse);

        $this->indexAction($request, $jsonResponse)->shouldBe($jsonResponse);
    }
}
