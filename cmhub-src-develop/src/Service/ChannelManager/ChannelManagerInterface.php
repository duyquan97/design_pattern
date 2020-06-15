<?php declare(strict_types=1);

namespace App\Service\ChannelManager;

use App\Entity\Booking;
use App\Model\PushBooking;

/**
 * Interface ChannelManagerInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface ChannelManagerInterface
{
    /**
     *
     * @param PushBooking $pushBooking
     *
     * @return void
     */
    public function pushBookingRequest(PushBooking $pushBooking);

    /**
     *
     * @param string $channelManagerCode
     *
     * @return bool
     */
    public function supports(string $channelManagerCode): bool;

    /**
     * @param Booking $booking
     *
     * @return string
     */
    public function getRequestBody(Booking $booking): string;

    /**
     * @return string
     */
    public function getContentType(): string;
}
