<?php

namespace App\Controller;

use App\Exception\CmHubException;
use App\Exception\FormValidationException;
use App\Service\ChannelManager\BB8\BB8Integration;
use App\Service\ChannelManager\BB8\Operation\GetAvailabilityOperation;
use App\Service\ChannelManager\BB8\Operation\GetBookingsOperation;
use App\Service\ChannelManager\BB8\Operation\GetPriceOperation;
use App\Service\ChannelManager\BB8\Operation\GetRoomsOperation;
use App\Service\ChannelManager\BB8\Operation\UpdateAvailabilityOperation;
use App\Service\ChannelManager\BB8\Operation\UpdatePriceOperation;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BB8Controller
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BB8Controller
{
    /**
     *
     * @var BB8Integration
     */
    private $integration;

    /**
     *
     * @var CmhubLogger $logger
     */
    private $logger;

    /**
     * AvailProController constructor.
     *
     * @param BB8Integration $integration
     * @param CmhubLogger    $logger
     */
    public function __construct(BB8Integration $integration, CmhubLogger $logger)
    {
        $this->integration = $integration;
        $this->logger = $logger;
    }

    /**
     * Gets the availability action.
     *
     * @param Request      $request
     * @param JsonResponse $jsonResponse
     *
     * @return Response
     */
    public function getAvailabilityAction(Request $request, JsonResponse $jsonResponse): Response
    {
        try {
            $this->logger->addOperationInfo(LogAction::GET_AVAILABILITY);

            return $jsonResponse->setData($this->integration->handle($request, GetAvailabilityOperation::NAME));
        } catch (\Exception $exception) {
            return $this->handleException($exception, LogAction::GET_AVAILABILITY, $jsonResponse);
        }
    }

    /**
     * Post availability action.
     *
     * @param Request      $request
     * @param JsonResponse $jsonResponse
     *
     * @return Response
     */
    public function postAvailabilityAction(Request $request, JsonResponse $jsonResponse): Response
    {
        try {
            $this->logger->addOperationInfo(LogAction::UPDATE_AVAILABILITY);

            return $jsonResponse->setData($this->integration->handle($request, UpdateAvailabilityOperation::NAME));
        } catch (\Exception $exception) {
            return $this->handleException($exception, LogAction::UPDATE_AVAILABILITY, $jsonResponse);
        }
    }

    /**
     * @param Request      $request
     * @param JsonResponse $jsonResponse
     *
     * @return Response
     */
    public function getRoomsAction(Request $request, JsonResponse $jsonResponse): Response
    {
        try {
            $this->logger->addOperationInfo(LogAction::GET_PRODUCTS);
            $data = $request->query->all();
            if (empty($data)) {
                throw new CmHubException('externalPartnerIds is mandatory');
            }

            return $jsonResponse->setData($this->integration->handle($request, GetRoomsOperation::NAME));
        } catch (\Exception $exception) {
            return $this->handleException($exception, LogAction::GET_PRODUCTS, $jsonResponse);
        }
    }

    /**
     * @param Request      $request
     * @param JsonResponse $jsonResponse
     *
     * @return Response
     */
    public function getPricesAction(Request $request, JsonResponse $jsonResponse): Response
    {
        try {
            $this->logger->addOperationInfo(LogAction::GET_PRICES);

            return $jsonResponse->setData($this->integration->handle($request, GetPriceOperation::NAME));
        } catch (\Exception $exception) {
            return $this->handleException($exception, LogAction::GET_PRICES, $jsonResponse);
        }
    }

    /**
     * Post price action.
     *
     * @param Request      $request
     * @param JsonResponse $jsonResponse
     *
     * @return Response
     */
    public function postPricesAction(Request $request, JsonResponse $jsonResponse): Response
    {
        try {
            $this->logger->addOperationInfo(LogAction::UPDATE_RATES);

            return $jsonResponse->setData($this->integration->handle($request, UpdatePriceOperation::NAME));
        } catch (\Exception $exception) {
            return $this->handleException($exception, LogAction::UPDATE_RATES, $jsonResponse);
        }
    }

    /**
     *
     * @param JsonResponse $jsonResponse
     *
     * @return Response
     */
    public function smokeTestAction(JsonResponse $jsonResponse): Response
    {
        return $jsonResponse->setData();
    }

    /**
     * @param Request      $request
     * @param JsonResponse $jsonResponse
     *
     * @return Response
     */
    public function getBookingsAction(Request $request, JsonResponse $jsonResponse): Response
    {
        try {
            $this->logger->addOperationInfo(LogAction::GET_BOOKINGS);

            return $jsonResponse->setData($this->integration->handle($request, GetBookingsOperation::NAME));
        } catch (\Exception $exception) {
            return $this->handleException($exception, LogAction::GET_BOOKINGS, $jsonResponse);
        }
    }

    /**
     *
     * @param \Exception   $exception
     * @param string       $action
     * @param JsonResponse $jsonResponse
     *
     * @return JsonResponse
     */
    private function handleException(\Exception $exception, string $action, JsonResponse $jsonResponse)
    {
        $this
            ->logger
            ->addOperationException(
                $action,
                $exception,
                $this
            );

        if ($exception instanceof FormValidationException) {
            return $jsonResponse
                ->setData(
                    [
                        'error' => $exception->getErrors(),
                    ]
                )
                ->setStatusCode(400);
        }

        return $jsonResponse
            ->setData(
                [
                    'error' => $exception->getMessage(),
                ]
            )
            ->setStatusCode((!$exception instanceof CmHubException) ? 500 : ($exception->getCode() ? $exception->getCode() : 400));
    }
}
