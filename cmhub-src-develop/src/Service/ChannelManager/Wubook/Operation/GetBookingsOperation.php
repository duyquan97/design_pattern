<?php

namespace App\Service\ChannelManager\Wubook\Operation;

use App\Entity\Partner;
use App\Exception\ValidationException;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\Wubook\Serializer\BookingCollectionNormalizer;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;

/**
 * Class BookingProductNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GetBookingsOperation implements WubookOperationInterface
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
     * @var CmhubLogger
     */
    private $logger;

    /**
     * GetBookingsOperation constructor.
     *
     * @param BookingEngineInterface      $bookingEngine
     * @param BookingCollectionNormalizer $bookingCollectionNormalizer
     * @param CmhubLogger                 $logger
     */
    public function __construct(BookingEngineInterface $bookingEngine, BookingCollectionNormalizer $bookingCollectionNormalizer, CmhubLogger $logger)
    {
        $this->bookingEngine = $bookingEngine;
        $this->bookingCollectionNormalizer = $bookingCollectionNormalizer;
        $this->logger = $logger;
    }

    /**
     *
     * @param \stdClass $request
     * @param Partner   $partner
     *
     * @return array
     *
     * @throws ValidationException
     */
    public function handle(\stdClass $request, Partner $partner): array
    {
        if (!isset($request->data->start_time)) {
            throw new ValidationException('Dates must be defined');
        }

        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', $request->data->start_time);
        $endDate = new \DateTime();

        if (!$startDate || !$endDate) {
            throw new ValidationException('Date format has to be Y-m-d H:i:s');
        }

        $this->logger->addOperationInfo(
            LogAction::GET_BOOKINGS,
            $partner,
            $this
        );

        return $this->bookingCollectionNormalizer->normalize($this->bookingEngine->getBookings($startDate, $endDate, null, [$partner]));
    }

    /**
     *
     * @param string $operation
     *
     * @return bool
     */
    public function supports(string $operation): bool
    {
        return static::NAME === $operation;
    }
}
