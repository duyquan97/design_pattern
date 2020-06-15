<?php

namespace App\Exception;

use App\Model\PartnerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ProductNotFoundException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductNotFoundException extends CmHubException
{
    const MESSAGE = 'The product code `%s` for Partner `%s` is not registered in SBX Channel Manager.';
    const TYPE = 'product_not_found';

    /**
     *
     * @var string
     */
    private $productId;

    /**
     * ProductNotFoundException constructor.
     *
     * @param PartnerInterface $partner
     * @param string           $roomTypeCode
     * @param int              $statusCode
     */
    public function __construct(PartnerInterface $partner, string $roomTypeCode, int $statusCode = Response::HTTP_BAD_REQUEST)
    {
        $this->productId = $roomTypeCode;

        parent::__construct(sprintf(static::MESSAGE, $roomTypeCode, $partner->getIdentifier()), $statusCode);
    }

    /**
     *
     * @return string
     */
    public function getExceptionType(): string
    {
        return static::TYPE;
    }
}
