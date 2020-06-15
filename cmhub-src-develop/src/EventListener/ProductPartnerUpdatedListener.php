<?php

namespace App\EventListener;

use App\Entity\Product;
use App\Message\Factory\ProductPartnerUpdatedFactory;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class ProductPartnerUpdatedListener
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductPartnerUpdatedListener
{
    /**
     * @var MessageBusInterface $messageBus
     */
    private $messageBus;

    /**
     * @var ProductPartnerUpdatedFactory $messageFactory
     */
    private $messageFactory;

    /**
     * @var array $products
     */
    private $products = [];

    /**
     * ProductPartnerUpdatedListener constructor.
     *
     * @param MessageBusInterface           $messageBus
     * @param ProductPartnerUpdatedFactory  $messageFactory
     *
     */
    public function __construct(MessageBusInterface $messageBus, ProductPartnerUpdatedFactory $messageFactory)
    {
        $this->messageBus = $messageBus;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param PreUpdateEventArgs $eventArgs
     *
     * @return void
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $product = $eventArgs->getEntity();

        if (!$product instanceof Product) {
            return;
        }

        if (!$eventArgs->hasChangedField('partner')) {
            return;
        }

        $this->products[] = $product;
    }

    /**
     * @param PostFlushEventArgs $args
     *
     * @return void
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        foreach ($this->products as $product) {
            $this->messageBus->dispatch($this->messageFactory->create($product->getIdentifier()));
        }
    }
}
