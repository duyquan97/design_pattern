<?php

namespace spec\App\MessageHandler;

use App\Entity\Availability;
use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\ProductRate;
use App\Entity\TransactionChannel;
use App\Message\Factory\ProcessAvailabilityUpdateFactory;
use App\Message\Factory\ProcessRateUpdateFactory;
use App\Message\Factory\SyncDataFactory;
use App\Message\PartnerChannelManagerUpdated;
use App\Message\ProcessAvailabilityUpdate;
use App\Message\ProcessRateUpdate;
use App\Message\SyncData;
use App\MessageHandler\PartnerChannelManagerUpdatedHandler;
use App\Repository\AvailabilityRepository;
use App\Repository\ProductRateRepository;
use App\Service\Iresa\IresaBookingEngine;
use App\Service\Loader\PartnerLoader;
use App\Service\Synchronizer\AvailabilityForcedAlignment;
use App\Service\Synchronizer\AvailabilitySynchronizer;
use App\Service\Synchronizer\PriceForcedAlignment;
use App\Service\Synchronizer\PriceSynchronizer;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

class PartnerChannelManagerUpdatedHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PartnerChannelManagerUpdatedHandler::class);
    }

    function let
    (
        AvailabilityRepository $availabilityRepository,
        ProductRateRepository $productRateRepository,
        MessageBusInterface $messageBus,
        PartnerLoader $partnerLoader,
        SyncDataFactory $syncDataFactory
    )
    {
        $this->beConstructedWith($availabilityRepository, $productRateRepository, $messageBus, $partnerLoader, $syncDataFactory);
    }

    function it_doesnt_process_message_if_entities_not_found(
        PartnerChannelManagerUpdated $message, PartnerLoader $partnerLoader
    )
    {
        $message->getIdentifier()->willReturn('12345');
        $partnerLoader->find('12345')->willReturn(null);

        $this->shouldThrow(UnrecoverableMessageHandlingException::class)->during('__invoke', [$message]);
    }

    function it_process_message
    (
        PartnerChannelManagerUpdated $message,
        PartnerLoader $partnerLoader,
        Partner $partner,
        Product $product,
        AvailabilityRepository $availabilityRepository,
        ProductRateRepository $productRateRepository,
        MessageBusInterface $messageBus,
        SyncDataFactory $syncDataFactory,
        SyncData $syncData
    )
    {
        $message->getIdentifier()->willReturn('12345');
        $partnerLoader->find('12345')->willReturn($partner);
        $partner->getProducts()->willReturn([$product]);

        $productRateRepository->reset($product)->shouldBeCalled();
        $availabilityRepository->reset($product)->shouldBeCalled();
        $partner->getIdentifier()->willReturn('12345');
        $syncDataFactory->create('12345', AvailabilityForcedAlignment::TYPE)->willReturn($syncData);
        $syncDataFactory->create('12345', PriceForcedAlignment::TYPE)->willReturn($syncData);
        $messageBus->dispatch($syncData)->shouldBeCalled()->willReturn(new Envelope($syncData));

        $this->__invoke($message);
    }
}
