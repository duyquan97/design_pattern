<?php

namespace spec\App\EventListener;

use App\EventListener\ProductPartnerUpdatedListener;
use App\Entity\Partner;
use App\Entity\Product;
use App\Message\Factory\ProductPartnerUpdatedFactory;
use App\Message\ProductPartnerUpdated;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductPartnerUpdatedListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductPartnerUpdatedListener::class);
    }

    function let(
        MessageBusInterface $messageBus, ProductPartnerUpdatedFactory $messageFactory
    )
    {
        $this->beConstructedWith($messageBus, $messageFactory);
    }

    function it_add_product_if_partner_has_changed(PostFlushEventArgs $args, PreUpdateEventArgs $eventArgs, Product $product, MessageBusInterface $messageBus, ProductPartnerUpdatedFactory $messageFactory, ProductPartnerUpdated $message)
    {
        $eventArgs->getEntity()->willReturn($product);
        $eventArgs->hasChangedField('partner')->willReturn(true);
        $product->getIdentifier()->willReturn('235854');

        $this->preUpdate($eventArgs);

        $messageFactory->create('235854')->willReturn($message);

        $messageBus->dispatch($message)->shouldBeCalled()->willReturn(new Envelope($message));

        $this->postFlush($args);
    }

    function it_doesnt_add_product_if_has_not_changed_partner(PostFlushEventArgs $args, PreUpdateEventArgs $eventArgs, Product $product, MessageBusInterface $messageBus, ProductPartnerUpdatedFactory $messageFactory)
    {
        $eventArgs->getEntity()->willReturn($product);
        $eventArgs->hasChangedField('partner')->willReturn(false);

        $this->preUpdate($eventArgs);

        $product->getIdentifier()->shouldNotBeCalled();
        $messageFactory->create(Argument::any())->shouldNotBeCalled();
        $messageBus->dispatch(Argument::any())->shouldNotBeCalled();

        $this->postFlush($args);
    }

    function it_doesnt_add_product_if_entity_is_not_product(PostFlushEventArgs $args, PreUpdateEventArgs $eventArgs, Partner $entity, Product $product, MessageBusInterface $messageBus, ProductPartnerUpdatedFactory $messageFactory)
    {
        $eventArgs->getEntity()->willReturn($entity);
        $eventArgs->hasChangedField('partner')->willReturn(false);

        $this->preUpdate($eventArgs);

        $product->getIdentifier()->shouldNotBeCalled();
        $messageFactory->create(Argument::any())->shouldNotBeCalled();
        $messageBus->dispatch(Argument::any())->shouldNotBeCalled();

        $this->postFlush($args);
    }
}
