<?php

namespace App\Controller\Admin;

use App\Exception\IresaClientException;
use App\Message\Factory\SyncDataFactory;
use App\Model\PartnerInterface;
use App\Service\Iresa\IresaBookingEngine;
use App\Service\Synchronizer\AvailabilityForcedAlignment;
use App\Service\Synchronizer\PriceForcedAlignment;
use GuzzleHttp\Exception\GuzzleException;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class PartnerAdminController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerAdminController extends CRUDController
{
    /** @var IresaBookingEngine $bookingEngine */
    private $bookingEngine;

    /**
     * @var SyncDataFactory
     */
    private $messageFactory;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * PartnerAdminController constructor.
     *
     * @param IresaBookingEngine  $bookingEngine
     * @param SyncDataFactory     $messageFactory
     * @param MessageBusInterface $messageBus
     */
    public function __construct(IresaBookingEngine $bookingEngine, SyncDataFactory $messageFactory, MessageBusInterface $messageBus)
    {
        $this->bookingEngine = $bookingEngine;
        $this->messageFactory = $messageFactory;
        $this->messageBus = $messageBus;
    }

    /**
     * @return RedirectResponse
     */
    public function refreshRoomAction()
    {
        /** @var PartnerInterface $partner */
        $partner = $this->admin->getSubject();

        try {
            $products = $this->bookingEngine->pullProducts($partner);

            if (count($products->getProducts()) > 0) {
                $this->addFlash('sonata_flash_success', sprintf('%d products have been successfully imported', count($products->getProducts())));
            }
            if (count($products->getProducts()) === 0) {
                $this->addFlash('sonata_flash_error', sprintf('Import failed. %d products found', count($products->getProducts())));
            }
        } catch (IresaClientException $exception) {
            $this->addFlash('sonata_flash_error', sprintf('Import failed. Exception message: %s', $exception->getResponse()));
        } catch (GuzzleException|\Exception $exception) {
            $this->addFlash('sonata_flash_error', sprintf('Import failed. Exception message: %s', $exception->getMessage()));
        }

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * @return RedirectResponse
     */
    public function dataAlignmentAction()
    {
        /** @var PartnerInterface $partner */
        $partner = $this->admin->getSubject();

        $this->messageBus->dispatch($this->messageFactory->create($partner->getIdentifier(), AvailabilityForcedAlignment::TYPE));
        $this->messageBus->dispatch($this->messageFactory->create($partner->getIdentifier(), PriceForcedAlignment::TYPE));

        $this->addFlash('sonata_flash_success', sprintf('Partner %s is processing data alignment for Availability and Price', $partner->getName()));

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
