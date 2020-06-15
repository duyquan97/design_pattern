<?php

namespace App\Service\ChannelManager\Wubook\Operation;

use App\Entity\Partner;
use App\Model\ProductInterface;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;

/**
 * Class GetRoomsOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GetRoomsOperation implements WubookOperationInterface
{
    public const NAME = 'get_rooms';

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
     * GetRoomsOperation constructor.
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
     * @param \stdClass $request
     * @param Partner   $partner
     *
     * @return array
     */
    public function handle(\stdClass $request, Partner $partner): array
    {
        $products = $this->productLoader->getByPartner($partner);

        $this->logger->addOperationInfo(
            LogAction::GET_PRODUCTS,
            $partner,
            $this
        );

        return [
            'hotel_id' => $partner->getIdentifier(),
            'rooms'    =>
                array_map(
                    function (ProductInterface $product) {
                        return [
                            'room_id' => $product->getIdentifier(),
                            'name'    => $product->getName(),
                        ];
                    },
                    $products->toArray()
                ),
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
