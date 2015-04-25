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
use IteratorAggregate;
use PHPUnit_Framework_TestCase;
use Traversable;

/**
 * Class IterablesTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class IterablesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldFilterOutNullElementsFromIterable()
    {
        $object = new ArrayObject([1, 2, null, 3, null]);
        $result = Iterables::filter($object, Predicates::notNull());
        self::assertTrue(Iterables::equal(new ArrayObject([1, 2, 3]), $result));
    }

    /**
     * @test
     */
    public function shouldReturnTheSize()
    {
        $object = new ArrayObject([1, 2, null, 3, null]);
        self::assertEquals(5, Iterables::size($object));
        self::assertEquals(5, Iterables::size(new TraversableWrapper($object)));
        self::assertEquals(5, Iterables::size(new TraversableWrapper($object->getIterator())));
    }

    /**
     * @test
     */
    public function shouldReturnContains()
    {
        $object = new ArrayObject([1, 2, null, 3, null]);
        self::assertTrue(Iterables::contains($object, 1));
        self::assertTrue(Iterables::contains(new TraversableWrapper($object), 2));
        self::assertTrue(Iterables::contains(new TraversableWrapper($object->getIterator()), 3));
    }

    /**
     * @test
     */
    public function shouldCheckIsEmpty()
    {
        $object = new ArrayObject([1, 2, null, 3, null]);
        self::assertFalse(Iterables::isEmpty($object));
        self::assertFalse(Iterables::isEmpty(new TraversableWrapper($object)));
        self::assertFalse(Iterables::isEmpty(new TraversableWrapper($object->getIterator())));
        self::assertTrue(Iterables::isEmpty(new ArrayObject([])));
    }

    /**
     * @test
     */
    public function shouldConcat()
    {
        $object1 = new ArrayObject([1, 2]);
        $object2 = new ArrayObject([3, 4]);
        self::assertTrue(Iterables::equal(Iterables::concat($object1, $object2), new ArrayObject([1, 2, 3, 4])));

    }
}

class TraversableWrapper implements IteratorAggregate
{
    private $traversable;

    /**
     * TraversableWrapper constructor.
     * @param Traversable $traversable
     */
    public function __construct(Traversable $traversable)
    {
        $this->traversable = $traversable;
    }

    public function getIterator()
    {
        return $this->traversable;
    }
}
