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
 * Class CollectionsTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class CollectionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldUseBinaryStringComparison()
    {
        $heap = Collections::createHeap(StringComparator::$BINARY_CASE_INSENSITIVE);
        $heap->insert('a');
        $heap->insert('B');
        self::assertEquals('B', $heap->extract());
        self::assertEquals('a', $heap->extract());
    }

    /**
     * @test
     */
    public function shouldCompareReverseOrder()
    {
        $comparator = Collections::reverseOrder(StringComparator::$BINARY);
        self::assertGreaterThan(0, $comparator->compare('a', 'b'));
    }

    /**
     * @test
     */
    public function shouldCompareComparable()
    {
        $heap = Collections::createHeap();
        $heap->insert(NumberFixture::$ONE);
        $heap->insert(NumberFixture::$TWO);
        self::assertSame(NumberFixture::$TWO, $heap->extract());
        self::assertSame(NumberFixture::$ONE, $heap->extract());
    }

    /**
     * @test
     */
    public function shouldCompareWithReverseNaturalOrder()
    {
        self::assertGreaterThan(0, Collections::reverseOrder()->compare(NumberFixture::$ONE, NumberFixture::$TWO));
    }

    /**
     * @test
     */
    public function shouldSortArrayObject()
    {
        $obj = new ArrayObject([NumberFixture::$TWO, NumberFixture::$ONE]);
        Collections::sort($obj);
        $iterator = $obj->getIterator();
        self::assertSame(NumberFixture::$ONE, $iterator->current());
        $iterator->next();
        self::assertSame(NumberFixture::$TWO, $iterator->current());
    }

    /**
     * @test
     */
    public function shouldSortArrayObjectWithComparator()
    {
        $obj = new ArrayObject(['b', 'a']);
        Collections::sort($obj, StringComparator::$BINARY);
        $iterator = $obj->getIterator();
        self::assertSame('a', $iterator->current());
        $iterator->next();
        self::assertSame('b', $iterator->current());
    }
}
