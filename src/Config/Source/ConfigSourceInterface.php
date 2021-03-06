<?php namespace buildr\Config\Source;

use buildr\Config\Selector\ConfigSelector;

/**
 * Common interface for config sources
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Config\Source
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
interface ConfigSourceInterface {

    /**
     * Returns a short, unique name for this config source
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the current environment name as string
     *
     * @return null|string
     */
    public function getEnvironmentName();

    /**
     * Get a configuration value by selector
     *
     * @param \buildr\Config\Selector\ConfigSelector $selector
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public function get(ConfigSelector $selector, $defaultValue = NULL);

}
