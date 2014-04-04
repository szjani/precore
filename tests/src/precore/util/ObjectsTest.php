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

use PHPUnit_Framework_TestCase;
use precore\lang\Object;
use precore\lang\ObjectInterface;

class ObjectsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function bothNullEqual()
    {
        self::assertTrue(Objects::equal(null, null));
    }

    /**
     * @test
     */
    public function equalByEquals()
    {
        $string = 'Hello World!';
        $str1 = new String($string);
        $str2 = new String($string);
        self::assertTrue(Objects::equal($str1, $str2));
    }

    /**
     * @test
     */
    public function checkEqualityIfSecondParameterIsNull()
    {
        $string = 'Hello World!';
        $str1 = new String($string);
        self::assertFalse(Objects::equal($str1, null));
    }

    /**
     * @test
     */
    public function scalarEqual()
    {
        self::assertTrue(Objects::equal(1, '1'));
    }
}

class String extends Object
{
    private $data;

    public function __construct($data)
    {
        $this->data = (string) $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function equals(ObjectInterface $object = null)
    {
        if ($object === null) {
            return false;
        }
        if ($object instanceof self) {
            return $this->data === $object->data;
        }
        return false;
    }
}
