<?php

namespace App\Service\ChannelManager\Wubook\Operation;

use App\Entity\Partner;
use App\Exception\ValidationException;
use App\Model\RatePlanCode;
use App\Service\BookingEngineInterface;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;

/**
 * Class GetDataOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GetDataOperation implements WubookOperationInterface
{
    public const NAME = 'get_data';

    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * @var BookingEngineInterface
     */
    private $bookingEngine;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     *
     * GetDataOperation constructor.
     *
     * @param ProductLoader          $productLoader
     * @param BookingEngineInterface $bookingEngine
     * @param CmhubLogger            $logger
     */
    public function __construct(ProductLoader $productLoader, BookingEngineInterface $bookingEngine, CmhubLogger $logger)
    {
        $this->productLoader = $productLoader;
        $this->bookingEngine = $bookingEngine;
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
        $response = [
            'hotel_id' => $partner->getIdentifier(),
            'rooms'    => [],
        ];

        if (!isset($request->data)) {
            throw new ValidationException('"data" is mandatory');
        }

        if (!isset($request->data->start_date, $request->data->end_date)) {
            throw new ValidationException('Dates must be defined');
        }

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2]\d|3[0-1])$/', $request->data->start_date)) {
            throw new ValidationException('Start date format has to be Y-m-d');
        }

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2]\d|3[0-1])$/', $request->data->end_date)) {
            throw new ValidationException('End date format has to be Y-m-d');
        }

        $startDate = \DateTime::createFromFormat('Y-m-d', $request->data->start_date);
        $endDate = \DateTime::createFromFormat('Y-m-d', $request->data->end_date);

        if ($startDate > $endDate) {
            throw new ValidationException('Start date cannot be greater than end date');
        }

        $products = $this->productLoader->getByPartner($partner);
        $availabilities = $this->bookingEngine->getAvailabilities($partner, $startDate, $endDate, $products->toArray());
        $rates = $this->bookingEngine->getRates($partner, $startDate, $endDate, $products->toArray());

        foreach ($products->getProducts() as $product) {
            $room = [
                'room_id' => $product->getIdentifier(),
                'days'    => [],
            ];
            $cursor = clone $startDate;
            while ($cursor <= $endDate) {
                $availability = $availabilities->getByProductAndDate($product, $cursor);
                $rate = $rates->getByProductAndDate($product, $cursor);
                $room['days'][$cursor->format('Y-m-d')] = [
                    'availability' => $availability ? $availability->getStock() : 0,
                    'rates'        => [
                        [
                            'rate_id' => RatePlanCode::SBX,
                            'price'   => $rate ? $rate->getAmount() : 0,
                        ],
                    ],
                ];
                $cursor->modify('+1 day');
            }
            $response['rooms'][] = $room;
        }

        $this->logger->addOperationInfo(
            LogAction::GET_DATA,
            $partner,
            $this
        );

        return $response;
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
