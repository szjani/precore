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

use PHPUnit_Framework_TestCase;

/**
 * Class NativeComparatorTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class StringComparatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCompareString()
    {
        self::assertLessThan(0, StringComparator::$BINARY->compare('a', 'b'));
        self::assertGreaterThan(0, StringComparator::$BINARY->compare('a', 'A'));
        self::assertGreaterThan(0, StringComparator::$BINARY->compare('2', '10'));
    }

    /**
     * @test
     */
    public function shouldCompareCaseInsensitiveString()
    {
        self::assertEquals(0, StringComparator::$BINARY_CASE_INSENSITIVE->compare('a', 'A'));
    }

    /**
     * @test
     */
    public function shouldUseNaturalOrdering()
    {
        self::assertLessThan(0, StringComparator::$NATURAL->compare('2', '10'));
        self::assertGreaterThan(0, StringComparator::$NATURAL->compare('a2', 'A10'));
    }

    /**
     * @test
     */
    public function shouldUseCaseInsensitiveNaturalOrdering()
    {
        self::assertLessThan(0, StringComparator::$NATURAL_CASE_INSENSITIVE->compare('a2', 'A10'));
    }
}
