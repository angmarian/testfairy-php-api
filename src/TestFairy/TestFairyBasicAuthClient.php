<?php
namespace TestFairy;

use Guzzle\Common\Collection;
use Guzzle\Service\Client;

class TestFairyBasicAuthClient extends TestFairyAbstractClient
{
    /** @var array The required config variables for this type of client */
    private static $required = array(
        'email',
        'api_key',
    );

    /**
     * Magic method used to retrieve a command
     *
     * @param string $method Name of the command object to instantiate
     * @param array  $args   Arguments to pass to the command
     *
     * @return mixed Returns the result of the command
     * @throws BadMethodCallException when a command is not found
     */
    public function __call($method, $args)
    {
        return json_decode(json_encode($this->getCommand($method, isset($args[0]) ? $args[0] : array())->getResult()));
    }

    /**
     * Creates a basic auth client with the supplied configuration options
     *
     * @param array $config
     * @return Client|TestFairyBasicAuthClient
     */
    public static function factory($config = [])
    {
        $client = new self();

        $config = Collection::fromConfig($config, $client->getDefaultConfig(), static::$required);

        $client->configure($config);

        $client->setBasicAuth($config->get('email'), $config->get('api_key'));

        $client->setUserAgent('testfairy-php/1.0.0', true);

        return $client;
    }
}
