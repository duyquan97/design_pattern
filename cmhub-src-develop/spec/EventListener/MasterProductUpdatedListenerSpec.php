<?php

namespace spec\App\EventListener;

use App\Entity\Product;
use App\EventListener\MasterProductUpdatedListener;
use App\Message\Factory\MasterProductUpdatedFactory;
use App\Message\MasterProductUpdated;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class MasterProductUpdatedListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MasterProductUpdatedListener::class);
    }

    function let(MessageBusInterface $messageBus, MasterProductUpdatedFactory $messageFactory)
    {
        $this->beConstructedWith($messageBus, $messageFactory);
    }

    function it_adds_products_with_master_product_field_updated(
        Product $product,
        PreUpdateEventArgs $eventArgs
    )
    {
        $eventArgs->getEntity()->willReturn($product);
        $product->getIdentifier()->willReturn('A');
        $eventArgs->hasChangedField('masterProduct')->willReturn(true);

        $this->preUpdate($eventArgs);
    }

    function it_dispatches_event_message_to_bus(
        PostFlushEventArgs $args,
        MessageBusInterface $messageBus,
        MasterProductUpdatedFactory $messageFactory,
        MasterProductUpdated $message,
        Product $product,
        PreUpdateEventArgs $eventArgs
    )
    {
        $messageFactory->create('A')->willReturn($message);
        $messageBus->dispatch($message)->shouldBeCalled()->willReturn(new Envelope($message));
        $this->it_adds_products_with_master_product_field_updated($product, $eventArgs);
        $this->postFlush($args);
    }
}
