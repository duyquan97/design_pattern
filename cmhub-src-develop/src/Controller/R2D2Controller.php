<?php

namespace App\Controller;

use App\Entity\Product;
use App\Exception\CmHubException;
use App\Repository\AvailabilityRepository;
use App\Repository\ProductRateRepository;
use App\Repository\ProductRepository;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class R2D2Controller
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class R2D2Controller
{
    public const START_DATE_PARAM = 'start';
    public const END_DATE_PARAM = 'end';

    /**
     * @var AvailabilityRepository
     */
    private $availabilityRepository;

    /**
     * @var ProductRateRepository
     */
    private $productRateRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var CmhubLogger
     */
    private $logger;

    /**
     * R2D2Controller constructor.
     *
     * @param AvailabilityRepository $availabilityRepository
     * @param ProductRateRepository $productRateRepository
     * @param ProductRepository $productRepository
     * @param CmhubLogger $logger
     */
    public function __construct(AvailabilityRepository $availabilityRepository, ProductRateRepository $productRateRepository, ProductRepository $productRepository, CmhubLogger $logger)
    {
        $this->availabilityRepository = $availabilityRepository;
        $this->productRateRepository = $productRateRepository;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param string $id      the product identifier
     *
     * @return array
     *
     * @throws CmHubException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function getAvailabilityAction(Request $request, string $id)
    {
        /** @var Product|null $product */
        $product = $this->productRepository->findOneBy(['identifier' => $id]);
        if (!$product) {
            $exception = new NotFoundHttpException(sprintf('Product identifier "%s" is not found in the system', $id));
            $this->logger->addOperationException(LogAction::R2D2_GET_AVAILABILITY, $exception, $this);
            throw $exception;
        }

        $start = $request->query->get(self::START_DATE_PARAM);
        $end = $request->query->get(self::END_DATE_PARAM);

        if (!$start || !$end) {
            $exception = new BadRequestHttpException('Either start date or end date is missing');
            $this->logger->addOperationException(LogAction::R2D2_GET_AVAILABILITY, $exception, $this);
            throw $exception;
        }

        $start = date_create_from_format('Y-m-d', $start);
        $end = date_create_from_format('Y-m-d', $end);

        if (false === $start || false === $end) {
            $exception = new BadRequestHttpException('Wrong date format. Expected format is "Y-m-d"');
            $this->logger->addOperationException(LogAction::R2D2_GET_AVAILABILITY, $exception, $this);
            throw $exception;
        }

        try {
            return $this->availabilityRepository->findByProductAndDate($product, $start, $end);
        } catch (\Doctrine\DBAL\DBALException $exception) {
            $this->logger->addOperationException(LogAction::R2D2_GET_AVAILABILITY, $exception, $this);
            throw new CmHubException("Internal Server Error", 500);
        }
    }

    /**
     * @param Request $request
     * @param string $id
     *
     * @return array
     *
     * @throws CmHubException
     */
    public function getPriceAction(Request $request, string $id)
    {
        /** @var Product|null $product */
        $product = $this->productRepository->findOneBy(['identifier' => $id]);
        if (!$product) {
            $exception = new NotFoundHttpException(sprintf('Product identifier "%s" is not found in the system', $id));
            $this->logger->addOperationException(LogAction::R2D2_GET_PRICE, $exception, $this);
            throw $exception;
        }

        $start = $request->query->get(self::START_DATE_PARAM);
        $end = $request->query->get(self::END_DATE_PARAM);

        if (!$start || !$end) {
            $exception = new BadRequestHttpException('Either start date or end date is missing');
            $this->logger->addOperationException(LogAction::R2D2_GET_PRICE, $exception, $this);
            throw $exception;
        }

        $start = date_create_from_format('Y-m-d', $start);
        $end = date_create_from_format('Y-m-d', $end);

        if (false === $start || false === $end) {
            $exception = new BadRequestHttpException('Wrong date format. Expected format is "Y-m-d"');
            $this->logger->addOperationException(LogAction::R2D2_GET_PRICE, $exception, $this);
            throw $exception;
        }

        try {
            return $this->productRateRepository->findByProductAndDate($product, $start, $end);
        } catch (\Doctrine\DBAL\DBALException $exception) {
            $this->logger->addOperationException(LogAction::R2D2_GET_PRICE, $exception, $this);
            throw new CmHubException("Internal Server Error", 500);
        }
    }
}
