<?php


namespace App\Controller\Admin;

use App\Exception\IresaClientException;
use App\Exception\PartnerNotFoundException;
use App\Form\Admin\GetAvailabilitiesType;
use App\Repository\PartnerRepository;
use App\Service\BookingEngineInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IresaAdminController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaAdminController extends CRUDController
{

    /**
     * @var BookingEngineInterface $iresaBookingEngine
     */
    protected $iresaBookingEngine;

    /**
     * @var PartnerRepository
     */
    protected $partnerRepository;

    /**
     * IresaAdminController constructor.
     *
     * @param BookingEngineInterface $iresaBookingEngine
     * @param PartnerRepository      $partnerRepository
     *
     *
     */
    public function __construct(BookingEngineInterface $iresaBookingEngine, PartnerRepository $partnerRepository)
    {
        $this->iresaBookingEngine = $iresaBookingEngine;
        $this->partnerRepository = $partnerRepository;
    }

    /**
     *
     * @return Response
     */
    public function listAction()
    {
        $form = $this->createForm(GetAvailabilitiesType::class);

        $availabilities = $error = [];
        try {
            $form->handleRequest($this->getRequest());
            if ($form->isSubmitted() && $form->isValid()) {
                $params = $form->getData();
                $partner = $this->partnerRepository->findOneBy(['identifier' => $partnerId = $params['partner_id']]);
                if (!$partner) {
                    throw new PartnerNotFoundException($partnerId);
                }
                $availabilities = $this->iresaBookingEngine->getAvailabilities($partner, $params['startDate'], $params['endDate'])->getAvailabilities();
            }
        } catch (IresaClientException $exception) {
            $error = $exception->getResponse();
        } catch (PartnerNotFoundException $exception) {
            $error = $exception->getMessage();
        }

        return $this->renderWithExtraParams(
            'Iresa/index.html.twig',
            [
                'availabilities' => $availabilities,
                'form'           => $form->createView(),
                'error'          => $error,
            ]
        );
    }
}
