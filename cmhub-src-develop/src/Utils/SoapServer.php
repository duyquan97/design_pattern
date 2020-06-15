<?php declare(strict_types=1);

namespace App\Utils;

use App\Service\ChannelManager\SoapOta\SoapOtaIntegration;

/**
 * Class SoapServer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SoapServer
{
    /**
     *
     * @var \SoapServer
     */
    private $server;

    /**
     *
     * @param \SoapServer        $server      The server
     * @param SoapOtaIntegration $integration The integration
     */
    public function __construct(\SoapServer $server, SoapOtaIntegration $integration)
    {
        $this->server = $server;
        $this->server->setObject($integration);
    }

    /**
     * Gets the response.
     *
     * @param string $request The request
     *
     * @return string The response.
     */
    public function getResponse(string $request): string
    {
        ob_start();
        $this->server->handle($request);

        return ob_get_clean();
    }
}
