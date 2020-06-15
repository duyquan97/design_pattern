<?php

namespace spec\App\Controller;

use App\Booking\BookingManager;
use App\Booking\Contract\Iresa\BookingType;
use App\Booking\Model\Booking;
use App\Controller\IresaController;
use App\Booking\Contract\Iresa\PushBookingType;
use App\Message\BookingReceived;
use App\Model\Factory\PushBookingFactory;
use App\Booking\Model\PushBooking;
use App\Utils\FormHelper;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogStatus;
use App\Utils\Monolog\LogType;
use Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class IresaControllerSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IresaController::class);
    }

    function let(FormFactoryInterface $formFactory, CmhubLogger $cmhubLogger, MessageBusInterface $messageBus, FormHelper $formHelper, BookingManager $bookingManager) {
        $this->beConstructedWith($formFactory, $cmhubLogger, $messageBus, $formHelper, $bookingManager);
    }
}
