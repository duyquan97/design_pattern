<?php

namespace App\Service\ChannelManager\BB8\Operation;

use App\Exception\FormValidationException;
use App\Form\Bb8GetBookingsType;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\BB8\Serializer\BookingCollectionNormalizer;
use App\Service\ChannelManager\ChannelManagerList;
use App\Utils\FormHelper;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class BookingProductNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GetBookingsOperation implements BB8OperationInterface
{
    public const NAME = 'get_bookings';

    /**
     * @var BookingEngineInterface
     */
    private $bookingEngine;

    /**
     * @var BookingCollectionNormalizer
     */
    private $bookingCollectionNormalizer;

    /**
     *
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     *
     * @var FormHelper
     */
    private $formHelper;

    /**
     * GetBookingsOperation constructor.
     *
     * @param BookingEngineInterface      $bookingEngine
     * @param BookingCollectionNormalizer $bookingCollectionNormalizer
     * @param FormFactoryInterface        $formFactory
     * @param FormHelper                  $formHelper
     */
    public function __construct(BookingEngineInterface $bookingEngine, BookingCollectionNormalizer $bookingCollectionNormalizer, FormFactoryInterface $formFactory, FormHelper $formHelper)
    {
        $this->bookingEngine = $bookingEngine;
        $this->bookingCollectionNormalizer = $bookingCollectionNormalizer;
        $this->formFactory = $formFactory;
        $this->formHelper = $formHelper;
    }

    /**
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws BadRequestHttpException
     * @throws FormValidationException
     */
    public function handle(Request $request): array
    {
        $data = $request->query->all();

        $form = $this->formFactory->create(Bb8GetBookingsType::class);
        $form->submit($data, true);

        if (!$form->isValid()) {
            $errors = $this->formHelper->getErrorsFromForm($form);

            throw new FormValidationException($errors);
        }

        $getBooking = $form->getData();
        $partners = $getBooking->getPartners();
        $bookingCollections = $this->bookingEngine->getBookings($getBooking->getStartDate(), $getBooking->getEndDate(), null, $partners);

        return $this->bookingCollectionNormalizer->normalize($bookingCollections);
    }

    /**
     *
     * @param string $operation
     *
     * @return bool
     */
    public function supports(string $operation): bool
    {
        return self::NAME === $operation;
    }
}
