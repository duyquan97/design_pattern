<?php

namespace App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Exception\ValidationException;
use App\Service\ChannelManager\SoapOta\SoapOtaOperationInterface;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;

/**
 * Class PingOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PingOperation implements SoapOtaOperationInterface
{
    public const OPERATION_NAME = 'OTA_PingRQ';

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * PingOperation constructor.
     *
     * @param CmhubLogger $logger
     */
    public function __construct(CmhubLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * @param \StdClass $request
     *
     * @return array
     *
     * @throws ValidationException
     */
    public function handle(\StdClass $request): array
    {
        $data = $request->EchoData;

        if (!$data) {
            throw new ValidationException('There is no message to show');
        }

        $this->logger->addOperationInfo(LogAction::PING, null, $this);

        return [
            'EchoData' => $data,
        ];
    }

    /**
     *
     * @param  string $operation The operation
     *
     * @return boolean
     */
    public function supports(string $operation): bool
    {
        return static::OPERATION_NAME === $operation;
    }
}
