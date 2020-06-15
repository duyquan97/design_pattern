<?php

namespace spec\App\Service\Broadcaster;

use App\Entity\Availability;
use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Exception\EAIClientException;
use App\Exception\EmptyRequestException;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\ProductAvailabilityCollection;
use App\Repository\AvailabilityRepository;
use App\Service\Broadcaster\EAIAvailabilityBroadcaster;
use App\Service\Chaining\ChainingHelper;
use App\Service\EAI\EAIProcessor;
use App\Service\EAI\EAIResponse;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class EAIAvailabilityBroadcasterSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EAIAvailabilityBroadcasterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EAIAvailabilityBroadcaster::class);
    }

    function let(
        AvailabilityRepository $availabilityRepository,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        EAIProcessor $EAIProcessor
    )
    {
        $this->beConstructedWith($availabilityRepository, $productAvailabilityCollectionFactory, $EAIProcessor);
    }

    function it_support(Transaction $transaction)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::EAI);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);

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
        $transaction->getType()->willReturn(TransactionType::PRICE);

        $this->support($transaction)->shouldBe(false);
    }

    function it_broadcast(
        Transaction $transaction,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        ProductAvailabilityCollection $collection,
        EAIProcessor $EAIProcessor,
        AvailabilityRepository $availabilityRepository,
        Availability $availability,
        Partner $partner,
        Product $product,
        EAIResponse $response
    )
    {
        $transaction->getPartner()->willReturn($partner);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $availabilityRepository->findBy(['transaction' => $transaction])->willReturn([$availability]);
        $availability->getPartner()->willReturn($partner);
        $productAvailabilityCollectionFactory->create($partner)->willReturn($collection);
        $collection->addAvailability($availability)->shouldBeCalled()->willReturn($collection);
        $availability->getProduct()->willReturn($product);
        $product->isMaster()->willReturn(true);
        $collection->isEmpty()->willReturn(false);
        $EAIProcessor->updateAvailabilities($collection)->willReturn($response)->shouldBeCalled();
        $availabilityRepository->clear()->shouldBeCalled();
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
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        ProductAvailabilityCollection $collection,
        EAIProcessor $EAIProcessor,
        AvailabilityRepository $availabilityRepository,
        Availability $availability,
        Partner $partner,
        Product $product
    )
    {
        $transaction->getPartner()->willReturn($partner);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $availabilityRepository->findBy(['transaction' => $transaction])->willReturn([$availability]);
        $availability->getPartner()->willReturn($partner);
        $productAvailabilityCollectionFactory->create($partner)->willReturn($collection);
        $collection->addAvailability($availability)->shouldBeCalled()->willReturn($collection);
        $availability->getProduct()->willReturn($product);
        $product->isMaster()->willReturn(true);
        $collection->isEmpty()->willReturn(true);

        $EAIProcessor->updateAvailabilities($collection)->shouldNotBeCalled();
        $this->shouldThrow(EmptyRequestException::class)->during('broadcast', [$transaction]);
    }

    function it_failed_to_broadcast(
        Transaction $transaction,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        ProductAvailabilityCollection $collection,
        EAIProcessor $EAIProcessor,
        AvailabilityRepository $availabilityRepository,
        Availability $availability,
        Partner $partner,
        Product $product
    )
    {
        $transaction->getPartner()->willReturn($partner);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $availabilityRepository->findBy(['transaction' => $transaction])->willReturn([$availability]);
        $availability->getPartner()->willReturn($partner);
        $productAvailabilityCollectionFactory->create($partner)->willReturn($collection);
        $collection->addAvailability($availability)->shouldBeCalled()->willReturn($collection);
        $availability->getProduct()->willReturn($product);
        $product->isMaster()->willReturn(true);
        $collection->isEmpty()->willReturn(false);

        $EAIProcessor->updateAvailabilities($collection)->willThrow(EAIClientException::class);
        $this->shouldThrow(EAIClientException::class)->during('broadcast', [$transaction]);
    }
}
