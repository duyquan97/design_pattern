<?php

namespace App\Controller;

use App\Booking\BookingManager;
use App\Booking\Contract\R2D2\BookingType;
use App\Booking\Model\Booking;
use App\Message\BookingReceived;
use App\Utils\FormHelper;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class BookingController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingController
{

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * BookingController constructor.
     *
     * @param MessageBusInterface $messageBus
     */
    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     *
     * @param Request              $request
     * @param FormFactoryInterface $formFactory
     * @param FormHelper           $formHelper
     *
     * @return Booking|array
     *
     * @Rest\View(statusCode=202)
     */
    public function create(Request $request, FormFactoryInterface $formFactory, FormHelper $formHelper)
    {
        $booking = new Booking();
        $form = $formFactory->create(BookingType::class, $booking);

        $form->submit(json_decode($request->getContent(), true));
        if (!$form->isValid()) {
            return [
                'errors' => $formHelper->getErrorsFromForm($form),
            ];
        }

        $this->messageBus->dispatch(new BookingReceived($booking));

        return $booking;
    }

    /**
     * @param string         $bookingId
     * @param BookingManager $bookingManager
     *
     * @return array
     *
     * @Rest\View(statusCode=202)
     */
    public function cancel(string $bookingId, BookingManager $bookingManager): array
    {
        $bookingManager->cancel($bookingId);

        return [
            'status' => 'success',
        ];
    }
}
