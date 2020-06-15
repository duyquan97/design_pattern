<?php

namespace App\Service\ChannelManager\Wubook\Operation;

use App\Entity\Partner;
use App\Model\ProductInterface;
use App\Model\Rate;
use App\Model\RatePlanCode;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;

/**
 * Class GetRatesOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GetRatesOperation implements WubookOperationInterface
{
    public const NAME = 'get_rates';

    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     *
     * GetRatesOperation constructor.
     *
     * @param ProductLoader $productLoader
     * @param CmhubLogger   $logger
     */
    public function __construct(ProductLoader $productLoader, CmhubLogger $logger)
    {
        $this->productLoader = $productLoader;
        $this->logger = $logger;
    }

    /**
     *
     * @param \stdClass $request
     * @param Partner   $partner
     *
     * @return array
     */
    public function handle(\stdClass $request, Partner $partner): array
    {
        $products = $this->productLoader->getByPartner($partner);

        $this->logger->addOperationInfo(
            LogAction::GET_RATE_PLANS,
            $partner,
            $this
        );

        return [
            'hotel_id' => $partner->getIdentifier(),
            'rates'    => [
                [
                    'rate_id'  => RatePlanCode::SBX,
                    'name'     => Rate::SBX_RATE_PLAN_NAME,
                    'currency' => $partner->getCurrency(),
                    'rooms'    => array_map(
                        function (ProductInterface $product) {
                            return $product->getIdentifier();
                        },
                        $products->toArray()
                    ),
                ],
            ],
        ];
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
