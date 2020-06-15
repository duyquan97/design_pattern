<?php

namespace App\Tests\Command;

use App\Message\PullRoom;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

/**
 * Class UpdateRoomsCommandTest
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class UpdateRoomsCommandTest extends WebTestCase
{
    protected static $application;

    public function setUp()
    {
        self::runCommand('cmhub:products:pull --partner="00019091,00127978"');
    }

    protected static function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return self::getApplication()->run(new StringInput($command));
    }

    protected static function getApplication()
    {
        if (null === self::$application) {
            $client = static::createClient();

            self::$application = new Application($client->getKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }

    public function testExecute()
    {
        $transport = self::$container->get('messenger.transport.default');
        $this->assertCount(2, $transport->get());
        foreach ($transport->get() as $message) {
            $this->assertInstanceOf(PullRoom::class, $message->getMessage());
        }
    }
}
