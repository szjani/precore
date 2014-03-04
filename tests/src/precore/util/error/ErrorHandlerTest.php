<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace precore\util\error;

use PHPUnit_Framework_TestCase;

class ErrorHandlerTest extends PHPUnit_Framework_TestCase
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
