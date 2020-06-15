<?php

namespace spec\App\Service\ChannelManager\BB8\Operation;

use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Exception\FormValidationException;
use App\Form\Bb8GetBookingsType;
use App\Model\BookingCollection;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\BB8\Operation\GetBookingsOperation;
use App\Service\ChannelManager\BB8\Operation\Model\GetBooking;
use App\Service\ChannelManager\BB8\Operation\Model\GetBookings;
use App\Service\ChannelManager\BB8\Serializer\BookingCollectionNormalizer;
use App\Service\Iresa\IresaBookingEngine;
use App\Utils\FormHelper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use DateTime;

class GetBookingsOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GetBookingsOperation::class);
    }

    function let(
        BookingEngineInterface $bookingEngine,
        BookingCollectionNormalizer $bookingCollectionNormalizer,
        FormFactoryInterface $formFactory,
        FormHelper $formHelper
    ) {
        $this->beConstructedWith(
            $bookingEngine,
            $bookingCollectionNormalizer,
            $formFactory,
            $formHelper
        );
    }

    function it_handle_success(
        Request $request,
        ParameterBag $parameterBag,
        FormFactoryInterface $formFactory,
        Form $form,
        BookingCollectionNormalizer $bookingCollectionNormalizer,
        BookingEngineInterface $bookingEngine,
        BookingCollection $bookingCollection,
        GetBookings $booking,
        Partner $partner,
        ChannelManager $channelManager
    )
    {
        $request->query = $parameterBag;

        $data = [
            'startDate'         => '2019-09-30',
            'endDate'           => '2019-10-01',
            'externalPartnerId' => '00019158',
        ];
        $parameterBag->all()->willReturn($data);
        $formFactory->create(Bb8GetBookingsType::class)->willReturn($form);
        $form->submit(Argument::any(), true)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $form->getData()->shouldBeCalled()->willReturn($booking);

        $booking->getPartners()->willReturn([$partner]);
        $startDate = new DateTime('2019-09-30');
        $endDate = new DateTime('2019-10-01');
        $booking->getStartDate()->willReturn($startDate);
        $booking->getEndDate()->willReturn($endDate);

        $bookingEngine->getBookings(
            Argument::that(
                function(\DateTime $date) {
                    return $date->format('Y-m-d') === '2019-09-30';
                }
            ),
            Argument::that(
                function(\DateTime $date) {
                    return $date->format('Y-m-d') === '2019-10-01';
                }
            ),
            null,
            [$partner]
        )->shouldBeCalled()->willReturn($bookingCollection);

        $bookingCollectionNormalizer->normalize($bookingCollection)->shouldBeCalled()->willReturn(['Bookings data']);

        $this->handle($request)->shouldBe(['Bookings data']);
    }

    function it_handle_fail(
        Request $request,
        ParameterBag $parameterBag,
        FormFactoryInterface $formFactory,
        Form $form,
        FormHelper $formHelper
    )
    {
        $request->query = $parameterBag;

        $data = [
            'startDate'         => '2019-09-30',
            'endDate'           => '2019-10-01',
            'externalPartnerId' => '00019158',
        ];
        $parameterBag->all()->willReturn($data);
        $formFactory->create(Bb8GetBookingsType::class)->willReturn($form);
        $form->submit(Argument::any(), true)->shouldBeCalled();
        $form->isValid()->willReturn(false);
        $formHelper->getErrorsFromForm($form)->willReturn(['']);
        $form->getData()->shouldNotBeCalled();
        $this->shouldThrow(FormValidationException::class)->during('handle', [$request]);
    }
}
