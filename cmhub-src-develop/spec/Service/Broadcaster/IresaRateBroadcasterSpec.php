<?php

namespace spec\App\Service\Broadcaster;

use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\ProductRate;
use App\Repository\ProductRateRepository;
use App\Exception\CmHubException;
use App\Exception\MissingTransactionDataException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\ProductRateCollection;
use App\Service\Broadcaster\IresaRateBroadcaster;
use App\Service\Chaining\ChainingHelper;
use App\Service\Iresa\IresaApi;
use PhpSpec\ObjectBehavior;

/**
 * Class IresaRateBroadcasterSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaRateBroadcasterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IresaRateBroadcaster::class);
    }

    function let(
        ProductRateCollectionFactory $productRateCollectionFactory,
        IresaApi $iresaApi,
        ProductRateRepository $productRateRepository,
        ChainingHelper $chainingHelper
    )
    {
        $this->beConstructedWith($productRateRepository, $productRateCollectionFactory, $iresaApi, $chainingHelper);
    }

    function it_support(Transaction $transaction)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);
        $transaction->getType()->willReturn(TransactionType::PRICE);

        $this->support($transaction)->shouldBe(true);
    }

    function it_does_not_support_channel(Transaction $transaction)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::EAI);

        $this->support($transaction)->shouldBe(false);
    }

    function it_does_not_support_type(Transaction $transaction)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);

        $this->support($transaction)->shouldBe(false);
    }

    function it_broadcast(
        Transaction $transaction,
        Transaction $savedTransaction,
        ProductRateCollectionFactory $productRateCollectionFactory,
        ProductRateCollection $collection,
        IresaApi $iresaApi,
        ProductRateRepository $productRateRepository,
        ProductRate $rate,
        Partner $partner,
        Product $product,
        ChainingHelper $chainingHelper
    )
    {
        $productRateRepository->findBy(['transaction' => $transaction])->willReturn([$rate]);
        $transaction->getPartner()->willReturn($partner);
        $transaction->getType()->willReturn(TransactionType::PRICE);
        $productRateCollectionFactory->create($partner)->willReturn($collection);
        $rate->getProduct()->willReturn($product);
        $collection->addRate($product, $rate)->shouldBeCalled()->willReturn($collection);
        $chainingHelper->chainRates($collection)->shouldBeCalled()->willReturn($collection);
        $iresaApi->updateRates($collection)->willReturn($collection);
        $productRateRepository->clear()->shouldBeCalled();
        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_failed_to_broadcast(
        Transaction $transaction,
        ProductRateCollectionFactory $productRateCollectionFactory,
        ProductRateCollection $collection,
        IresaApi $iresaApi,
        ProductRateRepository $productRateRepository,
        ProductRate $rate,
        Partner $partner,
        Product $product,
        ChainingHelper $chainingHelper
    )
    {
        $productRateRepository->findBy(['transaction' => $transaction])->willReturn([$rate]);
        $transaction->getPartner()->willReturn($partner);
        $transaction->getType()->willReturn(TransactionType::PRICE);
        $productRateCollectionFactory->create($partner)->willReturn($collection);
        $rate->getProduct()->willReturn($product);
        $collection->addRate($product, $rate)->shouldBeCalled()->willReturn($collection);
        $chainingHelper->chainRates($collection)->shouldBeCalled()->willReturn($collection);
        $iresaApi->updateRates($collection)->willThrow(CmHubException::class);
        $this->shouldThrow(CmHubException::class)->during('broadcast', [$transaction]);
    }

    function it_missing_transaction_data(
        ProductRateRepository $productRateRepository
    )
    {
        $productRateRepository->findBy(['transaction'])->willThrow(\ArgumentCountError::class);
        $this->shouldThrow(\ArgumentCountError::class)->during('broadcast', []);
    }

    function it_wrong_transaction_data(
        Transaction $transaction,
        ProductRateRepository $productRateRepository
    )
    {
        $productRateRepository->findBy(['transaction' => $transaction])->willThrow(MissingTransactionDataException::class);
        $this->shouldThrow(MissingTransactionDataException::class)->during('broadcast', [$transaction]);
    }
}