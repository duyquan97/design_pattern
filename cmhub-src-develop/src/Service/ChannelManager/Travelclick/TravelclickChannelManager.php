<?php

namespace App\Service\ChannelManager\Travelclick;

use App\Entity\Booking;
use App\Model\RatePlanCode;
use App\Service\ChannelManager\AbstractChannelManager;
use App\Service\ChannelManager\ChannelManagerInterface;
use App\Service\ChannelManager\ChannelManagerList;
use App\Utils\HttpClient;
use App\Utils\Monolog\CmhubLogger;
use Twig\Environment;

/**
 * Class TravelclickChannelManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class TravelclickChannelManager extends AbstractChannelManager implements ChannelManagerInterface
{
    const NAME = ChannelManagerList::TRAVELCLICK;

    /**
     *
     * @param HttpClient      $travelclickHttpClient The http client
     * @param Environment     $templating            The templating
     * @param CmhubLogger     $logger
     */
    public function __construct(HttpClient $travelclickHttpClient, Environment $templating, CmhubLogger $logger)
    {
        parent::__construct($travelclickHttpClient, $templating, $logger);
    }

    /**
     *
     * @param string $channelManagerCode
     *
     * @return bool
     */
    public function supports(string $channelManagerCode): bool
    {
        return static::NAME === $channelManagerCode;
    }

    /**
     * @param Booking $booking
     *
     * @return string
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getRequestBody(Booking $booking): string
    {
        return $this->templating->render(
            'Api/Ext/Soap/TravelClick/OTA_HotelResNotifRQ.xml.twig',
            [
                'booking' => $booking,
                'ratePlan' => RatePlanCode::SBX,
                'token' => bin2hex(random_bytes(8)),
                'timestamp' => (new \DateTime())->format(\DateTime::ISO8601),
            ]
        );
    }
}
