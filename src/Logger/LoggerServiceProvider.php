<?php namespace buildr\Logger;

use buildr\Logger\Attachment\MemoryUsageAttachment;
use buildr\Logger\Formatter\LineFormatter;
use buildr\Logger\Handler\StdOutHandler;
use Psr\Log\LoggerInterface;
use buildr\ServiceProvider\ServiceProviderInterface;
use buildr\Container\ContainerInterface;

/**
 * Service Provider for Logger
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Logger
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 *
 * @codeCoverageIgnore
 */
class LoggerServiceProvider implements ServiceProviderInterface {

    /**
     * Returns an object that be registered to registry
     *
     * @param \buildr\Container\ContainerInterface $container
     *
     * @return Object
     */
    public function register(ContainerInterface $container) {
        $logger = new Logger('buildrLogger');

        $stdOutHandler = new StdOutHandler();
        $stdOutHandler->setFormatter(new LineFormatter());

        $logger->pushHandler($stdOutHandler);
        $logger->pushAttachment(new MemoryUsageAttachment());

        return $logger;
    }

    /**
     * Return an array that contains interface bindings that
     * registered along with the provider.
     *
     * @return NULL|array
     */
    public function provides() {
        return [
            LoggerInterface::class,
        ];
    }

    /**
     * Returns the binding name in the registry
     *
     * @return string
     */
    public function getBindingName() {
        return "logger";
    }

}
