<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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
use precore\lang\ClassCastException;

class ComparisonChainTest extends PHPUnit_Framework_TestCase
{
    public function testWithClosures()
    {
        $strcmp = function ($left, $right) {
            return strcmp($left, $right);
        };
        $result = ComparisonChain::start()
            ->withClosure('aaa', 'aaa', $strcmp)
            ->withClosure('abc', 'bcd', $strcmp)
            ->withClosure('abc', 'bcd', $strcmp)
            ->result();
        self::assertTrue($result < 0);

        $result = ComparisonChain::start()
            ->withClosure('abc', 'abc', $strcmp)
            ->withClosure('bcd', 'abc', $strcmp)
            ->result();
        self::assertTrue(0 < $result);
    }

    /**
     * @test
     */
    public function expected0()
    {
        $strcmp = function ($left, $right) {
            return strcmp($left, $right);
        };
        $result = ComparisonChain::start()
            ->withClosure('aaa', 'aaa', $strcmp)
            ->result();
        self::assertTrue($result == 0);
    }

    public function testLazyWithComparator()
    {
        $comparator = $this->getMock('\precore\util\Comparator');
        $comparator
            ->expects(self::once())
            ->method('compare')
            ->will(self::returnValue(-1));
        $result = ComparisonChain::start()
            ->withComparator('abc', 'bcd', $comparator)
            ->withComparator('aaa', 'bbb', $comparator)
            ->result();
        self::assertTrue($result < 0);
    }
}
