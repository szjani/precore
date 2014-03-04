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
use precore\util\error\ErrorType;

class ErrorTypeTest extends PHPUnit_Framework_TestCase
{
    public function testForCode()
    {
        $error = ErrorType::forCode(E_ERROR);
        self::assertEquals(E_ERROR, $error->getCode());
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidForCode()
    {
        ErrorType::forCode(0);
    }

    /**
     * @expectedException \precore\util\error\UserWarningException
     */
    public function testThrowException()
    {
        ErrorType::forCode(E_USER_WARNING)->throwException('Ouch', __FILE__, __LINE__, array());
    }
}
