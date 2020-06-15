<?php

namespace spec\App\Service\Broadcaster;

use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\ProductRate;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Exception\EAIClientException;
use App\Exception\EmptyRequestException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\ProductRateCollection;
use App\Repository\ProductRateRepository;
use App\Service\Broadcaster\EAIRateBroadcaster;
use App\Service\Chaining\ChainingHelper;
use App\Service\EAI\EAIProcessor;
use App\Service\EAI\EAIResponse;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class EAIRateBroadcasterSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EAIRateBroadcasterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EAIRateBroadcaster::class);
    }

    function let(
        ProductRateCollectionFactory $productRateCollectionFactory,
        EAIProcessor $EAIProcessor,
        ProductRateRepository $productRateRepository
    )
    {
        $this->beConstructedWith($productRateRepository, $productRateCollectionFactory, $EAIProcessor);
    }

    function it_support(Transaction $transaction)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::EAI);
        $transaction->getType()->willReturn(TransactionType::PRICE);

        $this->support($transaction)->shouldBe(true);
    }

    function it_does_not_support_channel(Transaction $transaction)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);

        $this->support($transaction)->shouldBe(false);
    }

    function it_does_not_support_type(Transaction $transaction)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::EAI);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);

        $this->support($transaction)->shouldBe(false);
    }

    function it_broadcast(
        Transaction $transaction,
        ProductRateCollectionFactory $productRateCollectionFactory,
        ProductRateCollection $collection,
        EAIProcessor $EAIProcessor,
        ProductRateRepository $productRateRepository,
        ProductRate $rate,
        Partner $partner,
        Product $product,
        EAIResponse $response
    )
    {
        $transaction->getPartner()->willReturn($partner);
        $productRateRepository->findBy(['transaction' => $transaction])->willReturn([$rate]);
        $rate->getPartner()->willReturn($partner);
        $transaction->getType()->willReturn(TransactionType::PRICE);
        $productRateCollectionFactory->create($partner)->willReturn($collection);
        $rate->getProduct()->willReturn($product);
        $collection->addRate($product, $rate)->shouldBeCalled()->willReturn($collection);
        $collection->isEmpty()->willReturn(false);
        $EAIProcessor->updateRates($collection)->willReturn($response);
        $productRateRepository->clear()->shouldBeCalled();

        $response->getStatus()->willReturn($status = 'whatever');
        $response->getRequest()->willReturn($request = 'the_request');
        $response->getResponse()->willReturn($responseContent = 'the_response');
        $response->getStatusCode()->willReturn(200);
        $response->getTransactionId()->willReturn('whatever');
        $transaction->setRequest($request)->shouldBeCalled()->willReturn($transaction);
        $transaction->setTransactionId('whatever')->shouldBeCalled()->willReturn($transaction);
        $transaction->setResponse($responseContent)->shouldBeCalled()->willReturn($transaction);
        $transaction->setStatus($status)->shouldBeCalled()->willReturn($transaction);
        $transaction->setStatusCode(200)->shouldBeCalled()->willReturn($transaction);
        $transaction->setSentAt(Argument::type(\DateTime::class))->shouldBeCalled()->willReturn($transaction);


        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_does_not_broadcast_empty_collection(
        Transaction $transaction,
        ProductRateCollectionFactory $productRateCollectionFactory,
        ProductRateCollection $collection,
        EAIProcessor $EAIProcessor,
        ProductRateRepository $productRateRepository,
        ProductRate $rate,
        Partner $partner,
        Product $product
    )
    {
        $transaction->getPartner()->willReturn($partner);
        $productRateRepository->findBy(['transaction' => $transaction])->willReturn([$rate]);
        $rate->getPartner()->willReturn($partner);
        $transaction->getType()->willReturn(TransactionType::PRICE);
        $productRateCollectionFactory->create($partner)->willReturn($collection);
        $rate->getProduct()->willReturn($product);
        $collection->addRate($product, $rate)->shouldBeCalled()->willReturn($collection);
        $collection->isEmpty()->willReturn(true);
        $EAIProcessor->updateRates($collection)->shouldNotBeCalled();
        $this->shouldThrow(EmptyRequestException::class)->during('broadcast', [$transaction]);
    }

    function it_failed_to_broadcast(
        Transaction $transaction,
        ProductRateCollectionFactory $productRateCollectionFactory,
        ProductRateCollection $collection,
        EAIProcessor $EAIProcessor,
        ProductRateRepository $productRateRepository,
        ProductRate $rate,
        Partner $partner,
        Product $product
    )
    {
        $transaction->getPartner()->willReturn($partner);
        $productRateRepository->findBy(['transaction' => $transaction])->willReturn([$rate]);
        $rate->getPartner()->willReturn($partner);
        $transaction->getType()->willReturn(TransactionType::PRICE);
        $productRateCollectionFactory->create($partner)->willReturn($collection);
        $rate->getProduct()->willReturn($product);
        $collection->addRate($product, $rate)->shouldBeCalled()->willReturn($collection);
        $collection->isEmpty()->willReturn(false);
        $EAIProcessor->updateRates($collection)->willThrow(EAIClientException::class);

        $this->shouldThrow(EAIClientException::class)->during('broadcast', [$transaction]);
    }
}
