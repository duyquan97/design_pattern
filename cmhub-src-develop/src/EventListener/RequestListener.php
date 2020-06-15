<?php

namespace App\EventListener;

use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class RequestListener
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RequestListener
{
    /**
     *
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * RequestListener constructor.
     *
     * @param Stopwatch   $stopwatch
     * @param CmhubLogger $logger
     */
    public function __construct(Stopwatch $stopwatch, CmhubLogger $logger)
    {
        $this->stopwatch = $stopwatch;
        $this->logger = $logger;
    }

    /**
     *
     * @param RequestEvent $event
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $controllerMethod = $request->get('_controller');

        $this->stopwatch->start($controllerMethod);

        $this
            ->logger
            ->addRecord(
                \Monolog\Logger::INFO,
                'Kernel Request',
                [
                    LogKey::TYPE_KEY              => LogType::KERNEL_REQUEST_TYPE,
                    LogKey::HOST_KEY              => $request->getHost(),
                    LogKey::REQUEST_KEY           => $request->getContent(),
                    LogKey::CONTENT_TYPE_KEY      => $request->getContentType(),
                    LogKey::SCHEME_KEY            => $request->getScheme(),
                    LogKey::CLIENT_IP_KEY         => $request->getClientIp(),
                    LogKey::CONTROLLER_METHOD_KEY => $controllerMethod,
                    LogKey::URI_KEY               => $request->getUri(),
                ],
                $this
            );
    }

    /**
     *
     * @param ResponseEvent $event
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Set content-type
        if (strpos($request->getUri(), '/api/ext/') !== false) {
            $event->getResponse()->headers->set('Content-Type', 'text/xml; charset=utf-8');
            $event->getResponse()->headers->set('Content-Length', strlen($event->getResponse()->getContent()));
        }

        $controllerMethod = $request->get('_controller');
        if ($this->stopwatch->isStarted($controllerMethod)) {
            $stopWatchEvent = $this->stopwatch->stop($controllerMethod);

            $this
                ->logger
                ->addRecord(
                    \Monolog\Logger::INFO,
                    'Kernel Response. Total execution time: ' . $stopWatchEvent->getDuration() . ' & ' . sprintf('Max memory usage: ' . $stopWatchEvent->getMemory()),
                    [
                        LogKey::TYPE_KEY              => LogType::KERNEL_RESPONSE_TYPE,
                        LogKey::HOST_KEY              => $request->getHost(),
                        LogKey::REQUEST_KEY           => $request->getContent(),
                        LogKey::CONTENT_TYPE_KEY      => $request->getContentType(),
                        LogKey::SCHEME_KEY            => $request->getScheme(),
                        LogKey::CLIENT_IP_KEY         => $request->getClientIp(),
                        LogKey::MAX_MEMORY_USAGE_KEY  => $stopWatchEvent->getMemory(),
                        LogKey::CONTROLLER_METHOD_KEY => $controllerMethod,
                        LogKey::EXECUTION_TIME_KEY    => $stopWatchEvent->getDuration(),
                        LogKey::URI_KEY               => $request->getUri(),
                        LogKey::RESPONSE_KEY          => strpos($request->getUri(), '/api/ext/') ? $event->getResponse()->getContent() : '',
                        LogKey::STATUS_CODE_KEY       => $event->getResponse()->getStatusCode(),
                    ],
                    $this
                );
        }
    }
}
