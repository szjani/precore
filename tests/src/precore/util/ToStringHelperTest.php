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

namespace precore\util;

use DateTime;
use PHPUnit_Framework_TestCase;
use precore\util\error\ErrorHandler;

class ToStringHelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function oneNonNullProperty()
    {
        $obj = UUID::randomUUID();
        $helper = new ToStringHelper($obj->className());
        $value = $obj->toString();
        $string = $helper
            ->add('value', $value)
            ->toString();
        self::assertEquals($obj->className() . "{value=$value}", $string);
    }

    /**
     * @test
     */
    public function omitNullValues()
    {
        $helper = new ToStringHelper(__CLASS__);
        $string = $helper
            ->add('x', null)
            ->add('y', 'hello')
            ->omitNullValues()
            ->toString();
        self::assertEquals(sprintf('%s{y=hello}', __CLASS__), $string);
    }

    /**
     * @test
     */
    public function nullValueAppear()
    {
        $helper = new ToStringHelper(__CLASS__);
        $string = $helper
            ->add('x', null)
            ->add('y', 'hello')
            ->toString();
        self::assertEquals(sprintf('%s{x=null, y=hello}', __CLASS__), $string);
    }

    /**
     * @test
     */
    public function noFields()
    {
        $helper = new ToStringHelper(__CLASS__);
        $string = $helper->toString();
        self::assertEquals(sprintf('%s{}', __CLASS__), $string);
    }

    public function testDates()
    {
        $helper = new ToStringHelper(__CLASS__);
        $helper
            ->add('date', new DateTime())
            ->toString();
    }

    public function testStringCastError()
    {
        ErrorHandler::register();
        $helper = new ToStringHelper(__CLASS__);
        $helper
            ->add('object', new \stdClass())
            ->toString();
        restore_error_handler();
    }
}
