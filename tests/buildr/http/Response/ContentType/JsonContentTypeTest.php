<?php namespace buildr\tests\http\Response\ContentType;

use buildr\Http\Response\ContentType\JsonContentType;
use buildr\Http\Response\ContentType\Encoder\JsonContentEncoder;
use buildr\Http\Header\Writer\JsonHeaderWriter;
use buildr\tests\http\Response\ContentType\AbstractHttpContentTypeTest;

/**
 * Json content type test
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Tests\Http\Response\ContentType
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class JsonContentTypeTest extends AbstractHttpContentTypeTest {

    /**
     * Return a new instance from the tested encoder class
     *
     * @return \buildr\Http\Response\ContentType\HttpContentTypeInterface
     */
    public function getClassInstance() {
        return new JsonContentType();
    }

    /**
     * Returns the current content type unique encoder class anem, or NULL
     * if the content type has no specific encoder
     *
     * @return string|NULL
     */
    public function getEncoderName() {
        return JsonContentEncoder::class;
    }

    /**
     * Return the associated header writer FQ class name.
     *
     * @return string|null
     */
    function getHeaderWriterName() {
        return JsonHeaderWriter::class;
    }


}
