<?php

namespace spec\App\MessageHandler;

use App\Entity\Product;
use App\Message\Factory\SyncDataFactory;
use App\Message\MasterProductUpdated;
use App\Message\SyncData;
use App\MessageHandler\MasterProductUpdatedHandler;
use App\Model\PartnerInterface;
use App\Repository\AvailabilityRepository;
use App\Repository\ProductRateRepository;
use App\Service\Loader\ProductLoader;
use App\Service\Synchronizer\AvailabilityForcedAlignment;
use App\Service\Synchronizer\PriceForcedAlignment;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

class MasterProductUpdatedHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MasterProductUpdatedHandler::class);
    }

    function let(
        ProductLoader $productLoader, AvailabilityRepository $availabilityRepository, ProductRateRepository $productRateRepository, SyncDataFactory $messageFactory, MessageBusInterface $messageBus
    )
    {
        $this->beConstructedWith( $productLoader, $availabilityRepository, $productRateRepository, $messageFactory, $messageBus);
    }

    function it_process_message(
        MasterProductUpdated $message,
        ProductLoader $productLoader,
        Product $product,
        ProductRateRepository $productRateRepository,
        AvailabilityRepository $availabilityRepository,
        PartnerInterface $partner,
        SyncDataFactory $messageFactory,
        SyncData $syncData,
        SyncData $syncData1,
        MessageBusInterface $messageBus
    )
    {
        $message->getIdentifier()->willReturn('12345');
        $productLoader->getProductByIdentifier('12345')->willReturn($product);
        $product->isMaster()->shouldBeCalled()->willReturn(true);
        $availabilityRepository->reset($product)->shouldBeCalled();
        $productRateRepository->reset($product)->shouldBeCalled();
        $product->getPartner()->willReturn($partner);
        $partner->isEnabled()->shouldBeCalled()->willReturn(true);
        $partner->getIdentifier()->willReturn('11223344');

        $messageFactory->create('11223344', AvailabilityForcedAlignment::TYPE)->willReturn($syncData);
        $messageFactory->create('11223344', PriceForcedAlignment::TYPE)->willReturn($syncData1);
        $messageBus->dispatch($syncData)->shouldBeCalled()->willReturn(new Envelope($syncData));
        $messageBus->dispatch($syncData1)->shouldBeCalled()->willReturn(new Envelope($syncData1));

        $this->__invoke($message);
    }

    function it_process_message_not_sync(
        MasterProductUpdated $message,
        ProductLoader $productLoader,
        Product $product,
        ProductRateRepository $productRateRepository,
        AvailabilityRepository $availabilityRepository,
        PartnerInterface $partner,
        SyncDataFactory $messageFactory,
        MessageBusInterface $messageBus
    )
    {
        $message->getIdentifier()->willReturn('12345');
        $productLoader->getProductByIdentifier('12345')->willReturn($product);
        $product->isMaster()->shouldBeCalled()->willReturn(true);
        $availabilityRepository->reset($product)->shouldBeCalled();
        $productRateRepository->reset($product)->shouldBeCalled();
        $product->getPartner()->willReturn($partner);
        $partner->isEnabled()->shouldBeCalled()->willReturn(false);
        $partner->getIdentifier()->shouldNotBeCalled();

        $messageFactory->create(Argument::type('string'), AvailabilityForcedAlignment::TYPE)->shouldNotBeCalled();;
        $messageFactory->create(Argument::type('string'), PriceForcedAlignment::TYPE)->shouldNotBeCalled();;
        $messageBus->dispatch(Argument::type(SyncData::class))->shouldNotBeCalled();
        $messageBus->dispatch(Argument::type(SyncData::class))->shouldNotBeCalled();

        $this->__invoke($message);
    }

    function it_throw_exception(
        MasterProductUpdated $message,
        ProductLoader $productLoader
    )
    {
        $message->getIdentifier()->willReturn('12345');
        $productLoader->getProductByIdentifier('12345')->willReturn(null);
        $this->shouldThrow(UnrecoverableMessageHandlingException::class)->during('__invoke', [$message]);
    }
}
