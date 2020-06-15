<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\ChannelManager;
use App\Exception\ChannelManagerNotSupportedException;
use App\Repository\BookingRepository;
use App\Repository\ChannelManagerRepository;
use App\Service\ChannelManager\ChannelManagerResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TemplateController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class TemplateController
{
    /**
     * @var ChannelManagerRepository
     */
    private $channelManagerRepository;

    /**
     * @var BookingRepository
     */
    private $bookingRepository;

    /**
     * @var ChannelManagerResolver
     */
    private $channelManagerResolver;

    /**
     * TemplateController constructor.
     *
     * @param ChannelManagerRepository $channelManagerRepository
     * @param BookingRepository $bookingRepository
     * @param ChannelManagerResolver $channelManagerResolver
     */
    public function __construct(ChannelManagerRepository $channelManagerRepository, BookingRepository $bookingRepository, ChannelManagerResolver $channelManagerResolver)
    {
        $this->channelManagerRepository = $channelManagerRepository;
        $this->bookingRepository = $bookingRepository;
        $this->channelManagerResolver = $channelManagerResolver;
    }

    /**
     * @param string $cmId
     * @param string $bookingId
     *
     * @return Response
     *
     * @throws ChannelManagerNotSupportedException
     */
    public function renderAction(string $cmId, string $bookingId)
    {
        /** @var ChannelManager $channelManager */
        $channelManager = $this->channelManagerRepository->findOneBy(['identifier' => $cmId, 'pushBookings' => true]);
        if (!$channelManager) {
            throw new NotFoundHttpException(
                sprintf('Not found any push booking CM with id "%s"', $cmId)
            );
        }

        /** @var Booking $booking */
        $booking = $this->bookingRepository->findOneBy(['identifier' => $bookingId]);
        if (!$booking) {
            throw new NotFoundHttpException(
                sprintf('Not found any booking with id "%s"', $bookingId)
            );
        }

        $integration = $this->channelManagerResolver->getIntegration($channelManager);

        return new Response(
            $integration->getRequestBody($booking),
            200,
            [
                'Content-Type' => $integration->getContentType(),
            ]
        );
    }
}
