<?php namespace buildr\Filesystem;

use buildr\Filesystem\Types\Directory;
use buildr\Filesystem\Types\File;
use buildr\Utils\StringUtils;
use buildr\Filesystem\FilesystemInterface;

/**
 * Filesystem component
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Filesystem
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class Filesystem implements FilesystemInterface {

    private static $absoluteBasePath;

    /**
     * Constructor
     */
    public function __construct() {
        if((self::$absoluteBasePath = $this->detectBasePath()) === FALSE) {
            throw new \RuntimeException('Unable to instantiate the Filesystem class,
                                        because the absolute path detection is failed!');
        };
    }

    /**
     * Return the project absolute root path
     *
     * @return string
     */
    public static final function getProjectAbsoluteRoot() {
        return self::$absoluteBasePath;
    }

    /**
     * In initialization phase detects the absolute path of this project
     *
     * @return string
     */
    private final function detectBasePath() {
        return realpath(__DIR__ . DIRECTORY_SEPARATOR . self::assembleDirSystemSafe([
                '..',
                '..'
            ])) . DIRECTORY_SEPARATOR;
    }

    /**
     * Assemble dire by system-safe separator
     *
     * @param array $directoryParts
     *
     * @return string
     */
    public static final function assembleDirSystemSafe(array $directoryParts) {
        return implode(DIRECTORY_SEPARATOR, $directoryParts);
    }

    /**
     * Normalize directory, or file name by replacing any slashes to system-proper directory separator
     *
     * @param $path
     *
     * @return mixed
     */
    public final function normalizeSlashes($path) {
        $replacements = [
            "/" => DIRECTORY_SEPARATOR,
            "\\" => DIRECTORY_SEPARATOR,
            "//" => DIRECTORY_SEPARATOR,
            "\\\\" => DIRECTORY_SEPARATOR,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $path);
    }

    /**
     * Make a directory, or file location to absolute from a relative location
     *
     * @param $location
     *
     * @return string
     */
    public final function makeAbsolute($location) {
        $isAbsolute = FALSE;
        $location = $this->normalizeSlashes($location);
        $absoluteRoot = $this->getProjectAbsoluteRoot();

        if(StringUtils::contains($location, $absoluteRoot)) {
            $isAbsolute = TRUE;
        }

        if($isAbsolute === FALSE) {
            $location = realpath($absoluteRoot . $location);
        }

        return realpath($location);
    }

    /**
     * Touches a file. If not exist, it will create, or set the modification time
     * to current system time
     *
     * @param string $location relative to project absolute root
     * @param string $fileName
     *
     * @return bool
     */
    public final function touch($location, $fileName) {
        $fileLocation = $this->getProjectAbsoluteRoot() . $this->normalizeSlashes($location) .
                        DIRECTORY_SEPARATOR . $fileName;

        return touch($fileLocation);
    }

    /**
     * Get a File object for the specified file, that allows more
     * in-depth interaction with that file
     *
     * @param string $fileLocation
     *
     * @return \buildr\Filesystem\Types\File
     */
    public final function getFile($fileLocation) {
        $fileLocation = $this->makeAbsolute($fileLocation);

        if(!is_file($fileLocation)) {
            throw new \RuntimeException("The given file is not found!");
        }

        return new File($fileLocation);
    }

    /**
     * Get a Directory object for the specified directory, that allows more
     * in-depth interaction with it
     *
     * @param string $directory
     *
     * @return \buildr\Filesystem\Types\Directory
     */
    public final function getDirectory($directory) {
        $directoryLocation = $this->makeAbsolute($directory);

        if(!is_dir($directoryLocation)) {
            throw new \RuntimeException('The given folder (' . $directoryLocation . ') is not found!');
        }

        return new Directory($directoryLocation);
    }

}
