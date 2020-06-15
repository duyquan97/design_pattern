<?php declare(strict_types=1);

namespace App\Tests\Utils;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SoapServerTest
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class SoapServerTest extends WebTestCase
{
    private $util;
    private $server;
    private $integration;

    /**
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->server = $this->createMock('\SoapServer');
        $this->integration = $this->createMock('App\Service\ChannelManager\SoapOta\SoapOtaIntegration');
        $this->util = new \App\Utils\SoapServer($this->server, $this->integration);
    }

    /**
     *
     * @return void
     */
    public function testGetResponse(): void
    {
        $this->server->expects($this->once())
                     ->method('handle')
                     ->with('<xml>ping</xml>')
                     ->will(
                         $this->returnCallback(
                             function () {
                                 echo 'test';
                             }
                         )
                     );

        $this->assertEquals('test', $this->util->getResponse('<xml>ping</xml>'));
    }
}
