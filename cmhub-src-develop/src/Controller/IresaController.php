<?php declare(strict_types=1);

namespace App\Controller;

use App\Booking\BookingManager;
use App\Booking\Contract\Iresa\BookingType;
use App\Booking\Model\Booking;
use App\Message\BookingReceived;
use App\Utils\FormHelper;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogStatus;
use App\Utils\Monolog\LogType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Monolog\Logger;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class IresaController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaController
{
    /**
     *
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     *
     * @var CmhubLogger
     */
    private $cmhubLogger;

    /**
     *
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * @var FormHelper
     */
    private $formHelper;

    /**
     * @var BookingManager
     */
    private $bookingManager;

    /**
     * IresaController constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param CmhubLogger          $cmhubLogger
     * @param MessageBusInterface  $messageBus
     * @param FormHelper           $formHelper
     * @param BookingManager       $bookingManager
     */
    public function __construct(FormFactoryInterface $formFactory, CmhubLogger $cmhubLogger, MessageBusInterface $messageBus, FormHelper $formHelper, BookingManager $bookingManager)
    {
        $this->formFactory = $formFactory;
        $this->cmhubLogger = $cmhubLogger;
        $this->messageBus = $messageBus;
        $this->formHelper = $formHelper;
        $this->bookingManager = $bookingManager;
    }

    /**
     *
     * @param Request $request
     *
     * @return array
     *
     * @Rest\View()
     */
    public function pushBookingsAction(Request $request): array
    {
        $form = $this->formFactory->create(BookingType::class, $booking = new Booking());
        $form->handleRequest($request);

        if (!$form->isValid()) {
            $errors = $this->formHelper->getErrorsFromForm($form);
            $this
                ->cmhubLogger
                ->addRecord(
                    Logger::INFO,
                    'Iresa booking rejected',
                    [
                        LogKey::TYPE_KEY    => LogType::IRESA_TYPE,
                        LogKey::STATUS_KEY  => LogStatus::VALIDATION_ERROR,
                        LogKey::ACTION_KEY  => LogAction::PUSH_BOOKING,
                        LogKey::REQUEST_KEY => $request->getContent(),
                        LogKey::FIELD_ERROR => json_encode($errors),
                    ]
                );

            return [
                'status'          => 'success',
                'validation_info' => $errors,
            ];
        }

        $this
            ->cmhubLogger
            ->addRecord(
                Logger::INFO,
                'Iresa booking accepted',
                [
                    LogKey::TYPE_KEY    => LogType::IRESA_TYPE,
                    LogKey::STATUS_KEY  => LogStatus::SUCCESS,
                    LogKey::ACTION_KEY  => LogAction::PUSH_BOOKING,
                    LogKey::REQUEST_KEY => $request->getContent(),
                ]
            );

        if ($booking->isCancelled()) {
            $this->bookingManager->cancel($booking->getIdentifier());
        }

        if ($booking->isConfirmed()) {
            $this->messageBus->dispatch(new BookingReceived($booking));
        }

        return [
            'status' => 'success',
        ];
    }
}
