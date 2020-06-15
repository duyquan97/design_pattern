<?php

namespace spec\App\Controller;

use App\Controller\StandardController;
use App\Controller\TransactionController;
use App\Entity\Transaction;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\SoapServer;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

class TransactionControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TransactionController::class);
    }

    function it_update_status(
        Request $request,
        EntityManagerInterface $entityManager,
        ObjectRepository $objectRepository,
        Transaction $transaction,
        CmhubLogger $cmhubLogger
    )
    {
        $request->getContent()->willReturn(json_encode($this->requestData));
        $entityManager->getRepository(Transaction::class)->willReturn($objectRepository);
        $objectRepository->findOneBy(['transactionId' => 'sadasdsad.as1231asd'])->willReturn($transaction);
        $transaction->setStatus('success')->shouldBeCalled()->willReturn($transaction);
        $transaction->setStatusCode('200')->shouldBeCalled()->willReturn($transaction);
        $transaction->setResponse("{ \"data\": \"success\" }")->shouldBeCalled()->willReturn($transaction);
        $entityManager->persist($transaction)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $cmhubLogger->addOperationInfo(LogAction::EAI_UPDATE_CALLBACK, null, $this)->shouldBeCalled();

        $this->updateStatusAction($request, $entityManager, $cmhubLogger);
    }

    function it_update_status_throw_exception(
        Request $request,
        EntityManagerInterface $entityManager,
        CmhubLogger $cmhubLogger
    )
    {
        $request->getContent()->willReturn(json_encode($this->requestMissingData));
        $entityManager->getRepository(Transaction::class)->shouldNotBeCalled();

        $this->shouldThrow(BadRequestHttpException::class)->during('updateStatusAction', [$request, $entityManager, $cmhubLogger]);
    }

    protected $requestData = [
       "transaction_id" => "sadasdsad.as1231asd",
       "status" => "success",
       "statusCode" => 200,
       "response" => "{ \"data\": \"success\" }"
    ];

    protected $requestMissingData = [
       "transaction_id" => "sadasdsad.as1231asd",
       "statusCode" => 200,
       "response" => "{ \"data\": \"success\" }"
    ];
}