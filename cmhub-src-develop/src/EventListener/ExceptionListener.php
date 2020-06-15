<?php

namespace App\EventListener;

use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class ExceptionListener
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ExceptionListener
{
    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * @var Environment
     */
    private $engine;

    /**
     * @var string
     */
    private $environment;

    /**
     * ExceptionListener constructor.
     *
     * @param CmhubLogger     $logger
     * @param Environment $engine
     * @param string          $environment
     */
    public function __construct(CmhubLogger $logger, Environment $engine, string $environment)
    {
        $this->logger = $logger;
        $this->engine = $engine;
        $this->environment = $environment;
    }

    /**
     *
     * @param GetResponseForExceptionEvent $event
     *
     * @return void
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $this->logger->addRecord(
            \Monolog\Logger::INFO,
            $exception->getMessage(),
            [
                LogKey::TYPE_KEY    => LogType::EXCEPTION_TYPE,
                LogKey::EX_TYPE_KEY => 'unknown',
                LogKey::REQUEST_KEY => $event->getRequest()->getContent(),
                LogKey::MESSAGE_KEY => $exception->getMessage(),
            ],
            $this
        );

        if ('xml' === $event->getRequest()->getContentType()) {
            if ($exception instanceof NotFoundHttpException) {
                $event->setResponse(
                    new Response(
                        $this
                            ->engine
                            ->render(
                                'Api/Ext/Soap/Ota/Failure.xml.twig',
                                [
                                    'fault_code' => 404,
                                    'message'    => 'The url you are trying to access does not exists',
                                    'error_code' => 'Not Found',
                                ]
                            )
                    )
                );

                return;
            }

            $event->setResponse(
                new Response(
                    $this
                        ->engine
                        ->render(
                            'Api/Ext/Soap/Ota/Failure.xml.twig',
                            [
                                'fault_code' => 500,
                                'message'    => ($this->environment !== "prod") ? $exception->getMessage() : 'Internal Server Error. Please contact administrator',
                                'error_code' => 'Internal Server Error',
                            ]
                        )
                )
            );
        }
    }
}
