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
use ArrayObject;
use Iterator;
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
        self::assertTrue(Iterables::elementsEqual(new ArrayObject([1, 2, 3]), $result));
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
        self::assertTrue(Iterables::elementsEqual(Iterables::concat($object1, $object2), new ArrayObject([1, 2, 3, 4])));
    }

    /**
     * @test
     */
    public function shouldConcatIterables()
    {
        $object1 = new ArrayObject([1, 2]);
        $object2 = new ArrayObject([3, 4]);
        $object3 = new ArrayObject([5, 6]);
        $iterable = Iterables::concatIterables(new ArrayObject([$object1, $object2, $object3]));
        self::assertTrue(Iterables::elementsEqual($iterable, new ArrayObject([1, 2, 3, 4, 5, 6])));
    }

    /**
     * @test
     */
    public function shouldConcatIterators()
    {
        $object1 = new ArrayIterator([1, 2]);
        $object2 = new ArrayIterator([3, 4]);
        $object3 = new ArrayIterator([5, 6]);
        $iterable = Iterables::concatIterables(new ArrayObject([$object1, $object2, $object3]));
        self::assertTrue(Iterables::elementsEqual($iterable, new ArrayObject([1, 2, 3, 4, 5, 6])));
    }

    /**
     * @test
     */
    public function shouldFind()
    {
        self::assertEquals(1, Iterables::find(new ArrayObject([2, 1, 3]), Predicates::equalTo(1)));
    }

    /**
     * @test
     */
    public function shouldPartition()
    {
        $input = ['a', 'b', 'c', 'd', 'e'];
        $iterable = Iterables::fromArray($input);
        $result = Iterables::partition($iterable, 3);
        self::assertEquals(2, Iterables::size($result));

        $iterable = Iterables::fromArray($input);
        $result = Iterables::partition($iterable, 3);
        self::assertTrue(Iterables::elementsEqual(Iterables::fromArray(['a', 'b', 'c']), Iterables::get($result, 0)));
        self::assertTrue(Iterables::elementsEqual(Iterables::fromArray(['d', 'e']), Iterables::get($result, 1)));
    }

    /**
     * @test
     */
    public function shouldPaddedPartition()
    {
        $input = ['a', 'b', 'c', 'd', 'e'];
        $result = Iterables::paddedPartition(Iterables::fromArray($input), 3);
        self::assertEquals(2, Iterables::size($result));

        $result = Iterables::paddedPartition(Iterables::fromArray($input), 3);
        self::assertTrue(Iterables::elementsEqual(Iterables::fromArray(['a', 'b', 'c']), Iterables::get($result, 0)));
        self::assertTrue(Iterables::elementsEqual(Iterables::fromArray(['d', 'e', null]), Iterables::get($result, 1)));
    }

    /**
     * @test
     */
    public function shouldSkip()
    {
        $iterable = new ArrayObject([1, 2]);
        self::assertEquals([2], iterator_to_array(Iterables::skip($iterable, 1)));
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
