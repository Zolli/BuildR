<?php namespace buildr\Http\Response;

use buildr\Http\Constants\HttpProtocolVersion;
use buildr\Http\Header\HeaderBag;
use buildr\Http\Header\ResponseHeaderBag;
use buildr\Http\Constants\HttpResponseCode;
use buildr\Http\Response\ContentType\HtmlContentType;
use buildr\Http\Response\ContentType\HttpContentTypeInterface;
use buildr\Http\Response\ContentType\JsonContentType;
use buildr\Http\Response\ContentType\PlaintextContentType;

/**
 * Response object
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Http\Response
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class Response implements ResponseInterface {

    /**
     * @type mixed
     */
    private $body;

    /**
     * @type mixed
     */
    private $rawBody;

    /**
     * @type \buildr\Http\Header\ResponseHeaderBag
     */
    private $header;

    /**
     * @type NULL|string
     */
    private $statusCode;

    /**
     * @type string
     */
    private $statusText;

    /**
     * @type NULL|\buildr\Http\Response\ContentType\HttpContentTypeInterface
     */
    private $contentType;

    /**
     * @type string
     */
    private $contentTypeString;

    /**
     * @type NULL|string
     */
    private $protocolVersion;

    /**
     * @type bool
     */
    private $headerWriteOut = FALSE;

    /**
     * Constructor
     *
     * @param \buildr\Http\Header\ResponseHeaderBag|NULL $headers
     */
    public function __construct(ResponseHeaderBag $headers = NULL) {
        $this->header = $headers;
        $this->protocolVersion = (string) HttpProtocolVersion::HTTP_PROTOCOL_VERSION_1_1();

        if($headers === NULL) {
            $this->header = new ResponseHeaderBag();
        }
    }

    /**
     * Set the header write-out behaviour. If you set the Header write-out
     * to TRUE, it means that returned headers will be prepended to response,
     * if the corresponding content type returns the header.
     *
     * @param $value bool
     */
    public function setHeaderWriteOut($value) {
        $this->headerWriteOut = $value;
    }

    /**
     * Returns that the current response allow header
     * write-out or not.
     *
     * @return bool
     */
    public function isAllowingHeaderWriteOut() {
        return (bool) $this->headerWriteOut;
    }

    /**
     * Set the message body
     *
     * @param mixed $body
     */
    public function setBody($body) {
        $this->rawBody = $body;
        $this->body = $body;
    }

    /**
     * Same as the getBody() function, but is always return the unmodified,
     * original body content.
     *
     * @return mixed
     */
    public function getRawBody() {
        return $this->rawBody;
    }

    /**
     * Returns the HttpHeaderBag. This allow you to manipulate headers
     *
     * @return \buildr\Http\Header\ResponseHeaderBag
     */
    public function getHeaderBag() {
        return $this->header;
    }

    /**
     * Prepare the response. Fill the undefined statusCode and contentType if its not
     * specified, and run the encoding on message body.
     */
    private function prepare() {
        //If we not specified any response status
        if($this->statusCode === NULL) {
            $this->setStatusCode(HttpResponseCode::HTTP_OK());
        }

        //If the contentType is empty set a default
        if($this->contentType === NULL) {
            $this->setContentType(new PlaintextContentType());
        }
    }

    /**
     * Write out the specified headers
     *
     * @throws \LogicException
     */
    protected function sendHeaders() {
        //@codeCoverageIgnoreStart
        $headersSent = headers_sent($file, $line);

        if($headersSent === TRUE) {
            throw new \LogicException('The headers already sent! On: ' . $file . ':' . $line);
        }
        //@codeCoverageIgnoreEnd

        //Add the response status line
        $this->header->add(HeaderBag::HEADER_PROTOCOL, $this->protocolVersion);
        $this->header->add(HeaderBag::HEADER_STATUS_CODE, (string) $this->statusCode);
        $this->header->add(HeaderBag::HEADER_STATUS_TEXT, $this->statusText);

        //Add the content-type header to the bag
        $this->header->add('Content-Type', $this->contentTypeString);

        $headerResult = $this->contentType->getHeaderWriter()->write($this->header);

        //If we not allowing header write-out always returns NULL to prevent it
        if($this->isAllowingHeaderWriteOut() === FALSE) {
            return NULL;
        }

        return $headerResult;
    }

    /**
     * Send out the response, prepare, write out headers and
     * returns the finished response body
     *
     * @return mixed
     */
    public function send() {
        $this->prepare();

        $headerResult = $this->sendHeaders();

        //If the send headers not return bool or null value needs to be appended
        if(!is_bool($headerResult) && !is_null($headerResult)) {
            $this->body = $headerResult + $this->body;
        }

        //Encoding the body if needed
        if($this->contentType instanceof HttpContentTypeInterface) {
            if(($encoder = $this->contentType->getEncoder()) !== NULL) {
                $this->body = $encoder->encode($this->body);
            }
        }

        return $this->body;
    }

    /**
     * Shorthand function for send()
     *
     * @return string
     */
    public function __toString() {
        return $this->send();
    }

    /**
     * Set the status code of this response, by default this setter override
     * the status text, to the set status message. If you want to disable this
     * functionality, set the $disableStatusTextOverride to TRUE. If you disable
     * this override and not set the statusText manually the response MAYBE broken.
     *
     * @param \buildr\Http\Constants\HttpResponseCode $status
     * @param bool $disableStatusTextOverride
     */
    public function setStatusCode(HttpResponseCode $status, $disableStatusTextOverride = FALSE) {
        $this->statusCode = (string) $status;

        if($disableStatusTextOverride === FALSE) {
            $this->setStatusText($status->getMessage());
        }
    }

    /**
     * Set the status text manually
     *
     * @param mixed $statusText
     */
    public function setStatusText($statusText) {
        $this->statusText = (string) $statusText;
    }

    /**
     * Set the response HTTP protocol version
     *
     * @param \buildr\Http\Constants\HttpProtocolVersion $version
     */
    public function setProtocolVersion(HttpProtocolVersion $version) {
        $this->protocolVersion = (string) $version;
    }

    /**
     * Set the response content type. This is not so useful for returning plain text content
     * but useful if you want to encode the content before output. If you set the content type
     * using this method, by default override the content type string to the specified
     * content type. To disable this functionality set the secondary parameter to TRUE
     * and use the setContentTypeText() method to set the content type manually.
     *
     * @param \buildr\Http\Response\ContentType\HttpContentTypeInterface $contentType
     * @param bool $disableTypeOverride
     */
    public function setContentType(HttpContentTypeInterface $contentType, $disableTypeOverride = FALSE) {
        $this->contentType = $contentType;

        if($disableTypeOverride === FALSE) {
            $this->setContentTypeString($contentType->getMimeType());
        }
    }

    /**
     * Set the content type text to the specified. Useful for type overriding.
     *
     * @param string $contentType
     */
    public function setContentTypeString($contentType) {
        $this->contentTypeString = $contentType;
    }

    /**
     * Helper method to easily return HTML responses.
     *
     * @param string $body
     *
     * @return \buildr\Http\Response\ResponseInterface
     *
     * @codeCoverageIgnore
     */
    public function html($body) {
        $this->setContentType(new HtmlContentType());
        $this->setBody($body);

        return $this;
    }

    /**
     * Helper method to easily return JSON responses.
     *
     * @param array $body
     *
     * @return \buildr\Http\Response\ResponseInterface
     *
     * @codeCoverageIgnore
     */
    public function json($body) {
        $this->setContentType(new JsonContentType());
        $this->setBody($body);

        return $this;
    }

}
