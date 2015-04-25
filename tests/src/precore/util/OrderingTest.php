<?php
/*
 * Copyright (c) 2012-2015 Janos Szurovecz
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

use ArrayObject;
use PHPUnit_Framework_TestCase;

/**
 * Class OrderingTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class OrderingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldNullsFirst()
    {
        $array = ['a', 'c', null, 0, null, 'b'];
        Arrays::sort($array, Ordering::usingToString()->nullsFirst());
        self::assertEquals([null, null, '0', 'a', 'b', 'c'], $array);
    }

    /**
     * @test
     */
    public function shouldNullsLast()
    {
        $array = ['a', 'c', null, 0, null, 'b'];
        Arrays::sort($array, Ordering::usingToString()->nullsLast());
        self::assertEquals(['0', 'a', 'b', 'c', null, null], $array);
    }

    /**
     * @test
     */
    public function shouldReturnMin()
    {
        self::assertEquals('a', Ordering::usingToString()->min(new ArrayObject(['b', 'c', 'a', 'd'])));
    }

    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function shouldThrowExceptionMinOfEmptyInput()
    {
        Ordering::usingToString()->min(new ArrayObject([]));
    }

    /**
     * @test
     */
    public function shouldReturnMax()
    {
        self::assertEquals('d', Ordering::usingToString()->max(new ArrayObject(['b', 'c', 'd', 'a'])));
    }

    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function shouldThrowExceptionMaxOfEmptyInput()
    {
        Ordering::usingToString()->max(new ArrayObject([]));
    }

    /**
     * @test
     */
    public function shouldWorkSecondaryComparator()
    {
        $ordering = Ordering::from(StringComparator::$BINARY_CASE_INSENSITIVE)->compound(StringComparator::$BINARY);
        $array = ['a', 'A', 'a'];
        Arrays::sort($array, $ordering);
        self::assertEquals(['A', 'a', 'a'], $array);
    }
}
