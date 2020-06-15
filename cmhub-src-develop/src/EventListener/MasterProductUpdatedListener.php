<?php

namespace App\EventListener;

use App\Entity\Product;
use App\Message\Factory\MasterProductUpdatedFactory;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class MasterProductUpdatedListener
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class MasterProductUpdatedListener
{
    public const TIME_RANGE = '+3 years';

    /**
     * @var array $products
     */
    private $products = [];

    /**
     * @var MessageBusInterface $messageBus
     */
    private $messageBus;

    /**
     * @var MasterProductUpdatedFactory $messageFactory
     */
    private $messageFactory;

    /**
     * MasterProductUpdatedListener constructor.
     *
     * @param MessageBusInterface         $messageBus
     * @param MasterProductUpdatedFactory $messageFactory
     */
    public function __construct(MessageBusInterface $messageBus, MasterProductUpdatedFactory $messageFactory)
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

        if (!$eventArgs->hasChangedField('masterProduct')) {
            return;
        }

        $this->products[$product->getIdentifier()] = $product;
    }

    /**
     * @param PostFlushEventArgs $args
     *
     * @return void
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        /* @var Product $product */
        foreach ($this->products as $product) {
            $this->messageBus->dispatch($this->messageFactory->create($product->getIdentifier()));
        }
    }
}
