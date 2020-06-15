<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseWebTestCase extends WebTestCase
{
    /**
     * @var Application
     */
    public static $application;
    /**
     * @var Client
     */
    public $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $application = new Application(static::createClient()->getKernel());
        $application->setAutoExit(false);
        self::$application = $application;

        self::runConsoleCommand('doctrine:database:drop --if-exists --force --env=test --quiet');
        self::runConsoleCommand('doctrine:database:create --env=test --quiet');
        self::runConsoleCommand('doctrine:migrations:migrate --no-interaction --env=test --quiet');
    }

    public static function runConsoleCommand($command)
    {
        self::$application->run(new StringInput($command));
    }

    public function setUp()
    {
        $this->client = self::createClient();
    }

    /**
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return self::$kernel->getContainer();
    }

    public function getRepository(string $class)
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository($class);
    }
}
