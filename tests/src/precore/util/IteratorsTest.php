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

use ArrayIterator;
use PHPUnit_Framework_TestCase;

/**
 * Class IteratorsTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class IteratorsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldFilterOutNullElements()
    {
        $iterator = new ArrayIterator([1, 2, null, 3, null]);
        $result = Iterators::filter($iterator, Predicates::notNull());
        self::assertTrue(Iterators::equal($result, new ArrayIterator([1, 2, 3])));
    }

    /**
     * @test
     */
    public function shouldFilterOutEverything()
    {
        $iterator = new ArrayIterator([1, 2, null, 3, null]);
        $result = Iterators::filter($iterator, Predicates::alwaysFalse());
        self::assertTrue(Iterators::equal($result, new ArrayIterator()));
    }

    /**
     * @test
     */
    public function shouldIteratorsBeEqual()
    {
        $iterator1 = new ArrayIterator([1, 2, null, 3, null]);
        $iterator2 = new ArrayIterator([1, 2, null, 3, null]);
        self::assertTrue(Iterators::equal($iterator1, $iterator2));
        self::assertFalse(Iterators::equal($iterator1, new ArrayIterator()));
    }

    /**
     * @test
     */
    public function shouldReturnSize()
    {
        self::assertEquals(5, Iterators::size(new ArrayIterator([1, 2, null, 3, null])));
    }

    /**
     * @test
     */
    public function shouldCheckContains()
    {
        self::assertTrue(Iterators::contains(new ArrayIterator([1, 2, null, 3, null]), 1));
        self::assertFalse(Iterators::contains(new ArrayIterator([1, 2, null, 3, null]), 'nonexisting'));
    }

    /**
     * @test
     */
    public function shouldCheckIsEmpty()
    {
        self::assertFalse(Iterators::isEmpty(new ArrayIterator([1])));
        self::assertTrue(Iterators::isEmpty(new ArrayIterator([])));
    }

    /**
     * @test
     */
    public function shouldReturnIndex()
    {
        self::assertEquals(2, Iterators::get(new ArrayIterator([1, 2, null, 3, null]), 1));
    }

    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function shouldThrowExceptionIfIndexIsInvalid()
    {
        Iterators::get(new ArrayIterator([]), 0);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfLimitIsNegative()
    {
        Iterators::limit(new ArrayIterator([]), -1);
    }
}
