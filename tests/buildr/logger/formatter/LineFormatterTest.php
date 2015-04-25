<?php namespace buildr\tests\logger\formatter;

use buildr\Logger\Entry\LogEntry;
use buildr\Logger\Formatter\LineFormatter;
use Psr\Log\LogLevel;

/**
 * Line formatter tester
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Tests\Logger\Formatter
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class LineFormatterTest extends AbstractFormatterTester {

    protected function setUp() {
        $this->formatter = new LineFormatter();

        $date = new \DateTime();
        $attachments = ["testAttachmentKey" => "testAttachmentValue"];
        $this->entry = new LogEntry("My {value} message", ["value" => "test"], LogLevel::ERROR, $date, $attachments);

        parent::setUp();
    }

    function testItFormatCorrectly() {
        $result = $this->formatter->format($this->entry);
        $this->assertTrue(is_string($result));
    }


}
