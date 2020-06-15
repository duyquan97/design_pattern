<?php

namespace App\Service\ChannelManager\SoapOta;

use App\Exception\CmHubException;
use App\Exception\IresaClientException;
use App\Exception\SoapOtaOperationNotFoundException;
use App\Utils\Monolog\CmhubLogger;

/**
 * Class SoapOtaIntegration
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SoapOtaIntegration
{
    const VERSION = '1.0';

    /**
     *
     * @var SoapOtaOperationInterface[]
     */
    private $soapOtaOperations;

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
     *
     * @var string
     */
    private $namespace;

    /**
     * SoapOtaIntegration constructor.
     *
     * @param SoapOtaOperationInterface[] $soapOtaOperations
     * @param CmhubLogger $logger
     * @param string $environment
     * @param string $namespace
     */
    public function __construct(array $soapOtaOperations, CmhubLogger $logger, string $environment, string $namespace = '')
    {
        $this->soapOtaOperations = $soapOtaOperations;
        $this->logger = $logger;
        $this->environment = $environment;
        $this->namespace = $namespace;
    }

    /**
     *
     * @param string $operationName
     * @param array $args
     *
     * @return \StdClass
     */
    public function __call(string $operationName, array $args): \StdClass
    {
        if ('Security' === $operationName) {
            return new \StdClass();
        }

        try {
            foreach ($this->soapOtaOperations as $operation) {
                if ($operation->supports($operationName)) {
                    $this->logger
                        ->addOperationInfo(
                            $operationName,
                            null,
                            $this
                        );

                    return $this->success($operation->handle(current($args)), current($args)->EchoToken ?? '');
                }
            }

            throw new SoapOtaOperationNotFoundException();
        } catch (CmHubException $exception) {
            $this->logger->addOperationException($operationName, $exception, $this);

            return $this->error($exception, current($args));
        } catch (\Throwable $exception) {
            $this->logger->addOperationException($operationName, $exception, $this);

            return $this->error($exception, current($args));
        }
    }

    /**
     *
     * @param \Throwable $exception
     * @param \StdClass $args
     *
     * @return \StdClass
     */
    private function error(\Throwable $exception, \StdClass $args): \StdClass
    {

        $response = new \StdClass();
        $response->Errors = new \StdClass();
        $response->Errors->Error[0] = new \StdClass();
        $response->Errors->Error[0]->Code = $exception->getCode();
        $response->Errors->Error[0]->_ = $exception->getMessage();
        !isset($args->Version) ?: $response->Version = $args->Version ;
        !isset($args->TimeStamp) ?: $response->TimeStamp = $args->TimeStamp;
        !isset($args->EchoToken) ?: $response->EchoToken = $args->EchoToken;
        if ($exception instanceof IresaClientException && $this->environment === 'test') {
            $response->Errors->Error[0]->_ = $exception->getResponse();
        }

        if (!$exception instanceof CmHubException && $this->environment !== 'test') {
            $response->Errors->Error[0]->_ = 'An error occurred - Please contact administrator';
        }

        if ($this->environment === 'dev') {
            $response->Errors->Error[0]->_ = $exception->getMessage();
        }

        return $response;
    }

    /**
     * @param array|null $data
     * @param string $echoToken
     *
     * @return \StdClass
     */
    private function success(array $data = null, string $echoToken = ''): \StdClass
    {
        $response = (object) $data;
        $response->Version = static::VERSION;
        $response->TimeStamp = (new \DateTime())->format(\DateTime::ISO8601);
        $response->EchoToken = $echoToken;
        $response->Success = new \stdClass();
        if (!empty($this->namespace)) {
            $response->xmlns = $this->namespace;
        }

        return $response;
    }
}
