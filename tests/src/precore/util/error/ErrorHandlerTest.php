<?php

namespace precore\util\error;

use PHPUnit\Framework\TestCase;

class ErrorHandlerTest extends TestCase
{
    private $errorReporting;

    protected function setUp()
    {
        $this->errorReporting = error_reporting();
    }

    protected function tearDown()
    {
        error_reporting($this->errorReporting);
    }

    public function testTriggerError()
    {
        ErrorHandler::register();
        $message = 'Ouch';
        try {
            trigger_error($message, E_USER_NOTICE);
            self::fail('No exception thrown');
        } catch (UserNoticeException $e) {
            restore_error_handler();
            self::assertEquals($message, $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function disabledErrorReporting()
    {
        ErrorHandler::register();
        error_reporting(0);
        trigger_error('Ouch', E_ERROR);
        self::assertTrue(true);
    }
}
