<?php

namespace spec\App\Service\Broadcaster;

use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Exception\BroadcasterNotFoundException;
use App\Exception\CmHubException;
use App\Exception\EAIClientException;
use App\Exception\EmptyRequestException;
use App\Exception\IresaClientException;
use App\Exception\MissingTransactionDataException;
use App\Service\Broadcaster\BroadcastManager;
use App\Service\Broadcaster\EAIRateBroadcaster;
use App\Service\Broadcaster\IresaAvailabilityBroadcaster;
use App\Service\Broadcaster\IresaRateBroadcaster;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class BroadcastManagerSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BroadcastManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BroadcastManager::class);
    }

    function let(IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster, IresaRateBroadcaster $iresaRateBroadcaster, EAIRateBroadcaster $eaiRateBroadcaster, CmhubLogger $logger, EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith([$iresaAvailabilityBroadcaster, $iresaRateBroadcaster, $eaiRateBroadcaster], $logger, $entityManager, 5);
    }

    function it_get_broadcaster(Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster, IresaRateBroadcaster $iresaRateBroadcaster)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(true);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $this->getBroadcaster($transaction)->shouldBeAnInstanceOf(IresaAvailabilityBroadcaster::class);
    }

    function it_does_not_get_broadcaster(CmhubLogger $logger, Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster,
     IresaRateBroadcaster $iresaRateBroadcaster, EAIRateBroadcaster $eaiRateBroadcaster)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);
        $transaction->getType()->willReturn(TransactionType::BOOKING);
        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(false);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $eaiRateBroadcaster->support($transaction)->willReturn(false);

        $this->shouldThrow(BroadcasterNotFoundException::class)->during('getBroadcaster', [$transaction]);
    }

    function it_broadcast(CmhubLogger $logger, EntityManagerInterface $entityManager, Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster,
      IresaRateBroadcaster $iresaRateBroadcaster, EAIRateBroadcaster $eaiRateBroadcaster)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getStatus()->willReturn(TransactionStatus::SCHEDULED);
        $transaction->increaseRetries()->shouldNotBeCalled();
        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(true);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $eaiRateBroadcaster->support($transaction)->willReturn(false);
        $iresaAvailabilityBroadcaster->broadcast($transaction)->shouldBeCalledOnce()->willReturn($transaction);
        $transaction->setStatus(TransactionStatus::SUCCESS)->shouldBeCalledOnce()->willReturn($transaction);
        $transaction->setSentAt(
            Argument::that(function (\DateTime $start) {
                return $start->format('Y-m-d') === (new \DateTime())->format('Y-m-d');
            })
        )
        ->shouldBeCalled()
        ->willReturn($transaction);
        $entityManager->persist($transaction)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $transaction->toArray()->willReturn(['data']);
        $logger->addTransactionInfo($transaction, $this)->shouldBeCalled();

        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_failed_to_broadcast(EntityManagerInterface $entityManager, Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster,
        IresaRateBroadcaster $iresaRateBroadcaster, CmhubLogger $logger, EAIRateBroadcaster $eaiRateBroadcaster)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getStatus()->willReturn(TransactionStatus::SCHEDULED);
        $transaction->increaseRetries()->shouldNotBeCalled();
        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(true);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $eaiRateBroadcaster->support($transaction)->willReturn(false);

        $exception = (new CmHubException())->setMessage('message');
        $iresaAvailabilityBroadcaster->broadcast($transaction)->willThrow($exception);

        $transaction->setStatus(TransactionStatus::FAILED)->shouldBeCalledOnce()->willReturn($transaction);
        $transaction->setResponse('message')->shouldBeCalled()->willReturn($transaction);
        $entityManager->persist($transaction)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $transaction->toArray()->willReturn(['data']);
        $logger->addTransactionInfo($transaction, $this)->shouldBeCalled();


        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_retry_broadcast(CmhubLogger $logger, EntityManagerInterface $entityManager, Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster,
        IresaRateBroadcaster $iresaRateBroadcaster, EAIRateBroadcaster $eaiRateBroadcaster)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getStatus()->willReturn(TransactionStatus::FAILED);
        $transaction->getRetries()->willReturn(3);
        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(true);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $eaiRateBroadcaster->support($transaction)->willReturn(false);
        $iresaAvailabilityBroadcaster->broadcast($transaction)->shouldBeCalledOnce()->willReturn($transaction);
        $transaction->increaseRetries()->shouldBeCalledOnce();
        $transaction->setStatus(TransactionStatus::SUCCESS)->shouldBeCalledOnce()->willReturn($transaction);
        $transaction->setSentAt(
            Argument::that(function (\DateTime $start) {
                return $start->format('Y-m-d') === (new \DateTime())->format('Y-m-d');
            })
        )
            ->shouldBeCalled()
            ->willReturn($transaction);
        $entityManager->persist($transaction)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $transaction->toArray()->willReturn(['data']);
        $logger->addTransactionInfo($transaction, $this)->shouldBeCalled();

        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_retry_more_than_limit(CmhubLogger $logger, EntityManagerInterface $entityManager, Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster,
          IresaRateBroadcaster $iresaRateBroadcaster, EAIRateBroadcaster $eaiRateBroadcaster)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getStatus()->willReturn(TransactionStatus::FAILED);
        $transaction->getRetries()->willReturn(5);
        $transaction->increaseRetries()->shouldNotBeCalled();
        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(true);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $eaiRateBroadcaster->support($transaction)->willReturn(false);

        $exception = (new CmHubException())->setMessage('message');
        $iresaAvailabilityBroadcaster->broadcast($transaction)->willThrow($exception);
        $transaction->increaseRetries()->shouldBeCalledOnce();

        $transaction->setStatus(TransactionStatus::FAILED)->shouldBeCalledOnce()->willReturn($transaction);
        $transaction->setResponse('message')->shouldBeCalled()->willReturn($transaction);
        $transaction->setStatus(TransactionStatus::ERROR)->shouldBeCalled()->willReturn($transaction);
        $entityManager->persist($transaction)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $transaction->toArray()->willReturn(['data']);
        $logger->addTransactionInfo($transaction, $this)->shouldBeCalled();


        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_broadcast_EAI(CmhubLogger $logger, EntityManagerInterface $entityManager, Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster,
      IresaRateBroadcaster $iresaRateBroadcaster, EAIRateBroadcaster $eaiRateBroadcaster)
    {
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getStatus()->willReturn(TransactionStatus::SUCCESS);
        $transaction->getChannel()->willReturn(TransactionChannel::EAI);
        $transaction->increaseRetries()->shouldNotBeCalled();
        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(true);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $eaiRateBroadcaster->support($transaction)->willReturn(false);
        $iresaAvailabilityBroadcaster->broadcast($transaction)->shouldBeCalledOnce()->willReturn($transaction);
        $transaction->setStatus(TransactionStatus::SUCCESS)->shouldNotBeCalled();

        $transaction->setSentAt(
            Argument::that(function (\DateTime $start) {
                return $start->format('Y-m-d') === (new \DateTime())->format('Y-m-d');
            })
        )
            ->shouldBeCalled()
            ->willReturn($transaction);
        $entityManager->persist($transaction)->shouldBeCalledOnce();
        $entityManager->flush()->shouldBeCalledOnce();
        $transaction->toArray()->willReturn(['data']);
        $logger->addTransactionInfo($transaction, $this)->shouldBeCalled();

        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_throws_iresa_client_exception(Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster,
      IresaRateBroadcaster $iresaRateBroadcaster, EAIRateBroadcaster $eaiRateBroadcaster)
    {
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getStatus()->willReturn(TransactionStatus::SCHEDULED);
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);

        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(true);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $eaiRateBroadcaster->support($transaction)->willReturn(false);
        $iresaAvailabilityBroadcaster->broadcast($transaction)
            ->shouldBeCalledOnce()
            ->willThrow(new IresaClientException('Invalid Request', 400, 'Response'));

        $transaction->setStatus(TransactionStatus::FAILED)->shouldBeCalled()->willReturn($transaction);
        $transaction->setResponse('Response')->shouldBeCalled()->willReturn($transaction);

        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_throws_empty_request_exception(Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster,
           IresaRateBroadcaster $iresaRateBroadcaster, EAIRateBroadcaster $eaiRateBroadcaster)
    {
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getStatus()->willReturn(TransactionStatus::SCHEDULED);
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);

        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(true);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $eaiRateBroadcaster->support($transaction)->willReturn(false);
        $iresaAvailabilityBroadcaster->broadcast($transaction)
            ->shouldBeCalledOnce()
            ->willThrow(new EmptyRequestException());

        $transaction->setStatus(TransactionStatus::ERROR)->shouldBeCalled()->willReturn($transaction);
        $transaction->setResponse(EmptyRequestException::MESSAGE)->shouldBeCalled()->willReturn($transaction);

        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_throws_eai_client_exception(Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster,
        IresaRateBroadcaster $iresaRateBroadcaster, EAIRateBroadcaster $eaiRateBroadcaster)
    {
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getStatus()->willReturn(TransactionStatus::SCHEDULED);
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);

        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(false);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $eaiRateBroadcaster->support($transaction)->willReturn(true);
        $eaiRateBroadcaster->broadcast($transaction)
            ->shouldBeCalledOnce()
            ->willThrow(new EAIClientException('Request', 'Response', 400));

        $transaction->setStatus(TransactionStatus::FAILED)->shouldBeCalled()->willReturn($transaction);
        $transaction->setResponse('Response')->shouldBeCalled()->willReturn($transaction);
        $transaction->setRequest('Request')->shouldBeCalled()->willReturn($transaction);
        $transaction->setStatusCode(400)->shouldBeCalled()->willReturn($transaction);

        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_throws_missing_transaction_exception(Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster,
            IresaRateBroadcaster $iresaRateBroadcaster, EAIRateBroadcaster $eaiRateBroadcaster)
    {
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getStatus()->willReturn(TransactionStatus::SCHEDULED);
        $transaction->getChannel()->willReturn(TransactionChannel::EAI);

        $missingTransaction = new Transaction();
        $missingTransaction->setType('validation')->setTransactionId('123456789');

        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(false);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $eaiRateBroadcaster->support($transaction)->willReturn(true);
        $eaiRateBroadcaster->broadcast($transaction)
            ->shouldBeCalledOnce()
            ->willThrow(new MissingTransactionDataException($missingTransaction));

        $transaction->setStatus(TransactionStatus::DEPRECATED)->shouldBeCalled()->willReturn($transaction);
        $transaction->setResponse('Transaction type `validation` with id 123456789 related data has not been found')->shouldBeCalled()->willReturn($transaction);

        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_throws_guzzle_exception(Transaction $transaction, IresaAvailabilityBroadcaster $iresaAvailabilityBroadcaster,
             IresaRateBroadcaster $iresaRateBroadcaster, EAIRateBroadcaster $eaiRateBroadcaster)
    {
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getStatus()->willReturn(TransactionStatus::SCHEDULED);
        $transaction->getChannel()->willReturn(TransactionChannel::EAI);

        $iresaAvailabilityBroadcaster->support($transaction)->willReturn(false);
        $iresaRateBroadcaster->support($transaction)->willReturn(false);
        $eaiRateBroadcaster->support($transaction)->willReturn(true);
        $eaiRateBroadcaster->broadcast($transaction)
            ->shouldBeCalledOnce()
            ->willThrow(new BadResponseException('Invalid Request', new Request('POST', 'uri')));

        $transaction->setStatus(TransactionStatus::FAILED)->shouldBeCalled()->willReturn($transaction);
        $transaction->setResponse('Invalid Request')->shouldBeCalled()->willReturn($transaction);

        $this->broadcast($transaction)->shouldBe($transaction);
    }
}
