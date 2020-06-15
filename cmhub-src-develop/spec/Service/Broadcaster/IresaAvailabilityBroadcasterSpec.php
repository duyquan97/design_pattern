<?php

namespace spec\App\Service\Broadcaster;

use App\Entity\Availability;
use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Exception\CmHubException;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\ProductAvailabilityCollection;
use App\Repository\AvailabilityRepository;
use App\Service\Broadcaster\IresaAvailabilityBroadcaster;
use App\Service\Iresa\IresaApi;
use PhpSpec\ObjectBehavior;

/**
 * Class IresaAvailabilityBroadcasterSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaAvailabilityBroadcasterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IresaAvailabilityBroadcaster::class);
    }

    function let(
        AvailabilityRepository $availabilityRepository,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        IresaApi $iresaApi
    )
    {
        $this->beConstructedWith($availabilityRepository, $productAvailabilityCollectionFactory, $iresaApi);
    }

    function it_support(Transaction $transaction)
    {
        $transaction->getChannel()->willReturn(TransactionChannel::IRESA);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);

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
        $transaction->getType()->willReturn(TransactionType::PRICE);

        $this->support($transaction)->shouldBe(false);
    }

    function it_broadcast(
        Transaction $transaction,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        ProductAvailabilityCollection $collection,
        IresaApi $iresaApi,
        AvailabilityRepository $availabilityRepository,
        Availability $availability,
        Partner $partner,
        Product $product
    )
    {
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $availabilityRepository->findBy(['transaction' => $transaction])->willReturn([$availability]);
        $transaction->getPartner()->willReturn($partner);
        $productAvailabilityCollectionFactory->create($partner)->willReturn($collection);
        $collection->addAvailability($availability)->shouldBeCalled()->willReturn($collection);
        $availability->getProduct()->willReturn($product);
        $product->isMaster()->willReturn(true);

        $iresaApi->updateAvailabilities($collection)->willReturn($collection)->shouldBeCalled();
        $availabilityRepository->clear()->shouldBeCalled();
        $this->broadcast($transaction)->shouldBe($transaction);
    }

    function it_failed_to_broadcast(
        Transaction $transaction,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        ProductAvailabilityCollection $collection,
        IresaApi $iresaApi,
        AvailabilityRepository $availabilityRepository,
        Availability $availability,
        Partner $partner,
        Product $product
    )
    {
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $transaction->getType()->willReturn(TransactionType::AVAILABILITY);
        $availabilityRepository->findBy(['transaction' => $transaction])->willReturn([$availability]);
        $transaction->getPartner()->willReturn($partner);
        $productAvailabilityCollectionFactory->create($partner)->willReturn($collection);
        $collection->addAvailability($availability)->shouldBeCalled()->willReturn($collection);
        $availability->getProduct()->willReturn($product);
        $product->isMaster()->willReturn(true);

        $iresaApi->updateAvailabilities($collection)->willThrow(CmHubException::class);
        $this->shouldThrow(CmHubException::class)->during('broadcast', [$transaction]);
    }
}