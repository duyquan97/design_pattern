<?php

namespace App\Controller;

use App\Exception\CmHubException;
use App\Exception\IresaClientException;
use App\Exception\RatePlanNotFoundException;
use App\Model\WubookErrorCode;
use App\Service\ChannelManager\Wubook\WubookIntegration;
use App\Utils\Monolog\CmhubLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WubookController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WubookController
{
    /**
     *
     * @var WubookIntegration
     */
    private $wubookIntegration;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     *
     * @var string
     */
    private $environment;

    /**
     * WubookController constructor.
     *
     * @param WubookIntegration $wubookIntegration
     * @param CmhubLogger       $logger
     * @param string            $environment
     */
    public function __construct(WubookIntegration $wubookIntegration, CmhubLogger $logger, string $environment)
    {
        $this->wubookIntegration = $wubookIntegration;
        $this->logger = $logger;
        $this->environment = $environment;
    }

    /**
     *
     * @param Request      $request
     * @param JsonResponse $jsonResponse
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request, JsonResponse $jsonResponse)
    {
        $contentRequest = json_decode($request->getContent());

        try {
            $data = [
                'code' => 200,
            ];

            $response = $this->wubookIntegration->handle($contentRequest);
            if ($response) {
                $data['data'] = $response;
            }

            return $jsonResponse
                ->setData(
                    $data
                );
        } catch (IresaClientException $iresaClientException) {
            $this->logger->addOperationException(
                $contentRequest->action,
                $iresaClientException,
                $this
            );

            return $jsonResponse
                ->setData(
                    [
                        'code'  => Response::HTTP_BAD_REQUEST,
                        'error' => ($this->environment === 'test') ? $iresaClientException->getResponse() : $iresaClientException->getMessage(),
                    ]
                )
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        } catch (RatePlanNotFoundException $ratePlanNotFoundException) {
            $this->logger->addOperationException(
                $contentRequest->action,
                $ratePlanNotFoundException,
                $this
            );

            return $jsonResponse
                ->setData(
                    [
                        'error' => $ratePlanNotFoundException->getMessage(),
                        'code'  => WubookErrorCode::INVALID_RATE_ID,
                    ]
                )
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        } catch (CmHubException $exception) {
            $this->logger->addOperationException(
                $contentRequest->action,
                $exception,
                $this
            );

            return $jsonResponse
                ->setData(
                    [
                        'code'  => $exception->getCode(),
                        'error' => $exception->getMessage(),
                    ]
                )
                ->setStatusCode($exception->getCode());
        } catch (\Exception $exception) {
            $this->logger->addOperationException(
                $contentRequest->action,
                $exception,
                $this
            );

            return $jsonResponse
                ->setData(
                    [
                        'code'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                        'error' => 'Unexpected internal error. Please contact administrator.',
                    ]
                )
                ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
