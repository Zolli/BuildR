<?php namespace buildr\Startup\Environment\Detector;

use buildr\Startup\Environment\Detector\DetectorInterface;
use buildr\Config\Config;
use buildr\Startup\BuildrEnvironment;

/**
 * Environment detector by HTTP requests domain
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Startup\Environment\Detector
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class HTTPRequestDomainDetector implements DetectorInterface {

    /**
     * Returns the detected environment as string
     *
     * @return string
     */
    public function detect() {
        $envDetectionConfig = Config::getEnvDetectionConfig();
        $currentDomain = $_SERVER['HTTP_HOST'];

        foreach($envDetectionConfig['domains'] as $env => $domains) {
            if(($result = array_search($currentDomain, $domains)) !== FALSE) {
                return $env;
            }
        }

        return BuildrEnvironment::E_DEV;
    }
}
