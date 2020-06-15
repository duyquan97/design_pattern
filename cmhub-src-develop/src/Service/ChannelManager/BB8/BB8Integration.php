<?php

namespace App\Service\ChannelManager\BB8;

use App\Exception\BB8OperationNotFoundException;
use App\Exception\CmHubException;
use App\Service\ChannelManager\BB8\Operation\BB8OperationInterface;
use App\Utils\Monolog\CmhubLogger;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BB8Integration
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BB8Integration
{

    /**
     * @var BB8OperationInterface[]
     */
    private $operations;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * BB8Integration constructor.
     *
     * @param array       $operations
     * @param CmhubLogger $logger
     */
    public function __construct(array $operations, CmhubLogger $logger)
    {
        $this->operations = $operations;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param string  $action
     *
     * @return array
     *
     * @throws CmhubException
     * @throws BB8OperationNotFoundException
     */
    public function handle(Request $request, $action)
    {
        foreach ($this->operations as $operation) {
            if ($operation->supports($action)) {
                return $operation->handle($request);
            }
        }

        $this->logger->addRecord(
            Logger::ALERT,
            sprintf('BB8 Operation %s not found', $action),
            [],
            $this
        );

        throw new BB8OperationNotFoundException();
    }
}
