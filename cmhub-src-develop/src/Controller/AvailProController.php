<?php declare(strict_types=1);

namespace App\Controller;

use App\Exception\DateFormatException;
use App\Service\ChannelManager\AvailPro\AvailProIntegration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AvailProController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailProController
{
    /**
     *
     * @var AvailProIntegration
     */
    private $integration;

    /**
     * AvailProController constructor.
     *
     * @param AvailProIntegration $integration
     */
    public function __construct(AvailProIntegration $integration)
    {
        $this->integration = $integration;
    }

    /**
     * Gets the hotel action.
     *
     * @param Request  $request  The request
     * @param Response $response The response
     *
     * @return Response
     */
    public function getHotelAction(Request $request, Response $response): Response
    {
        return $response->setContent($this->integration->getHotel($request->get('hotelCode')));
    }

    /**
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     */
    public function updateAvailabilitiesAndRatesAction(Request $request, Response $response): Response
    {
        $xml = simplexml_load_string($request->getContent(), 'SimpleXMLElement', LIBXML_NOCDATA);
        $data = json_decode(json_encode($xml));

        return $response->setContent($this->integration->updateAvailabilitiesAndRates($data));
    }

    /**
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     *
     * @throws DateFormatException
     */
    public function getBookingsAction(Request $request, Response $response): Response
    {
        $startDate = new \DateTime();
        $endDate = new \DateTime();

        try {
            if (null !== $request->get('from') && null !== $request->get('to')) {
                $startDate = new \DateTime($request->get('from'));
                $endDate = new \DateTime($request->get('to'));
            } elseif (null !== $request->get('duration')) {
                $startDate = new \DateTime(sprintf('-%d hour', $request->get('duration')));
            }
        } catch (\Exception $exception) {
            throw new DateFormatException('c');
        }

        if (!$startDate || !$endDate) {
            throw new DateFormatException('c');
        }

        return $response->setContent($this->integration->getBookings($startDate, $endDate, $request->get('hotelId', '')));
    }
}
