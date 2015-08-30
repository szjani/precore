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
use EmptyIterator;
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
        self::assertTrue(Iterators::elementsEqual($result, new ArrayIterator([1, 2, 3])));
    }

    /**
     * @test
     */
    public function shouldFilterOutEverything()
    {
        $iterator = new ArrayIterator([1, 2, null, 3, null]);
        $result = Iterators::filter($iterator, Predicates::alwaysFalse());
        self::assertTrue(Iterators::elementsEqual($result, new ArrayIterator()));
    }

    /**
     * @test
     */
    public function shouldFilterIteratorWithMovedPointer()
    {
        $iterator = new ArrayIterator([1, 2, null, 3, null]);
        $iterator->rewind();
        $iterator->next();
        $iterator->next();
        $result = Iterators::filter($iterator, Predicates::notNull());
        self::assertTrue($result->valid());
        self::assertEquals(3, $result->current());
        $result->next();
        self::assertFalse($result->valid());
    }

    /**
     * @test
     */
    public function shouldIteratorsBeEqual()
    {
        $iterator1 = new ArrayIterator([1, 2, null, 3, null]);
        $iterator2 = new ArrayIterator([1, 2, null, 3, null]);
        $iterator3 = new ArrayIterator([1, 2, null, 3]);
        self::assertTrue(Iterators::elementsEqual($iterator1, $iterator2));
        $iterator1->rewind();
        self::assertFalse(Iterators::elementsEqual($iterator1, new ArrayIterator()));
        $iterator1->rewind();
        self::assertFalse(Iterators::elementsEqual($iterator1, $iterator3));
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
    public function shouldNotRewindContainCheck()
    {
        $it = new ArrayIterator([1, 2]);
        $it->next();
        self::assertFalse(Iterators::contains($it, 1));
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

    /**
     * @test
     */
    public function shouldNotRewindWhenLimiting()
    {
        $it = new ArrayIterator([1, 2]);
        $it->next();
        self::assertEquals(2, $it->current());
        $result = Iterators::limit($it, 1);
        self::assertTrue($result->valid());
    }

    /**
     * @test
     */
    public function shouldLimit()
    {
        $it = new ArrayIterator([1, 2]);
        $result = Iterators::limit($it, 1);
        self::assertEquals(1, Iterators::size($result));
    }

    /**
     * @test
     */
    public function shouldReturnConcat()
    {
        $it1 = new ArrayIterator([1, 2]);
        $it2 = new ArrayIterator([3, 4]);
        $result = Iterators::concatIterators(new ArrayIterator([$it1, $it2]));
        self::assertTrue(Iterators::elementsEqual(new ArrayIterator([1, 2, 3, 4]), $result));
    }

    /**
     * @test
     */
    public function shouldReturnEmptyConcat()
    {
        $it1 = new ArrayIterator([]);
        $it2 = new ArrayIterator([]);
        $result = Iterators::concatIterators(new ArrayIterator([$it1, $it2]));
        self::assertTrue(Iterators::isEmpty($result));
    }

    /**
     * @test
     */
    public function shouldHandleEmptyIterator()
    {
        $result = Iterators::concatIterators(new ArrayIterator([]));
        self::assertTrue(Iterators::isEmpty($result));
    }

    /**
     * @test
     */
    public function shouldHandleOneEmptyAndANotEmptyIterator()
    {
        $it1 = new ArrayIterator([]);
        $it2 = new ArrayIterator([1]);
        $it3 = new ArrayIterator([]);
        $it4 = new ArrayIterator([2]);
        $result = Iterators::concatIterators(new ArrayIterator([$it1, $it2, $it3, $it4]));
        self::assertTrue(Iterators::elementsEqual(new ArrayIterator([1, 2]), $result));
    }

    /**
     * @test
     */
    public function shouldReturnFrequency()
    {
        self::assertEquals(0, Iterators::frequency(new ArrayIterator([1]), 2));
        self::assertEquals(2, Iterators::frequency(new ArrayIterator([1, 2, null, 2, 3, 4]), 2));
        self::assertEquals(0, Iterators::frequency(new ArrayIterator([]), 1));
    }

    /**
     * @test
     */
    public function shouldNotRewindFrequency()
    {
        $it = new ArrayIterator([1, 2]);
        $it->next();
        self::assertEquals(0, Iterators::frequency($it, 1));
    }

    /**
     * @test
     */
    public function shouldNotRewindAll()
    {
        $it = new ArrayIterator([null, 2]);
        $it->next();
        self::assertTrue(Iterators::all($it, Predicates::notNull()));
    }

    /**
     * @test
     */
    public function shouldNotRewindIndexOf()
    {
        $it = new ArrayIterator([null, 2, null]);
        $it->next();
        self::assertEquals(1, Iterators::indexOf($it, Predicates::isNull()));
    }

    /**
     * @test
     */
    public function shouldPartitionEmptyIterator()
    {
        $iterator = Iterators::partition(new EmptyIterator(), 1);
        self::assertTrue(Iterators::isEmpty($iterator));
    }

    /**
     * @test
     */
    public function shouldPartitionToOneChunk()
    {
        $input = new ArrayIterator(['a']);
        $iterator = Iterators::partition($input, 1);
        self::assertEquals(1, Iterators::size($iterator));
        $iterator->rewind();
        self::assertTrue(Iterators::elementsEqual(new ArrayIterator(['a']), Iterators::get($iterator, 0)));
    }

    /**
     * @test
     */
    public function shouldPartitionToTwoChunks()
    {
        $input = new ArrayIterator(['a', 'b', 'c', 'd']);
        $iterator = Iterators::partition($input, 2);
        self::assertEquals(2, Iterators::size($iterator));
        $iterator->rewind();
        self::assertTrue(Iterators::elementsEqual(new ArrayIterator(['a', 'b']), Iterators::get($iterator, 0)));
        self::assertTrue(Iterators::elementsEqual(new ArrayIterator(['c', 'd']), Iterators::get($iterator, 1)));
    }

    /**
     * @test
     */
    public function shouldPartitionWithSmallerLastChunk()
    {
        $input = new ArrayIterator(['a', 'b', 'c']);
        $iterator = Iterators::partition($input, 2);
        self::assertEquals(2, Iterators::size($iterator));
        $iterator->rewind();
        self::assertTrue(Iterators::elementsEqual(Iterators::forArray(['a', 'b']), Iterators::get($iterator, 0)));
        self::assertTrue(Iterators::elementsEqual(Iterators::singletonIterator('c'), Iterators::get($iterator, 1)));
    }

    /**
     * @test
     */
    public function shouldPaddedPartitionToOneChunk()
    {
        $input = new ArrayIterator(['a']);
        $iterator = Iterators::paddedPartition($input, 1);
        self::assertEquals(1, Iterators::size($iterator));
        $iterator->rewind();
        self::assertTrue(Iterators::elementsEqual(new ArrayIterator(['a']), Iterators::get($iterator, 0)));
    }

    /**
     * @test
     */
    public function shouldPaddedPartitionToTwoChunks()
    {
        $input = new ArrayIterator(['a', 'b', 'c', 'd']);
        $iterator = Iterators::paddedPartition($input, 2);
        self::assertEquals(2, Iterators::size($iterator));
        $iterator->rewind();
        self::assertTrue(Iterators::elementsEqual(new ArrayIterator(['a', 'b']), Iterators::get($iterator, 0)));
        self::assertTrue(Iterators::elementsEqual(new ArrayIterator(['c', 'd']), Iterators::get($iterator, 1)));
    }

    /**
     * @test
     */
    public function shouldPaddedPartitionWithNullFilledLastChunk()
    {
        $input = new ArrayIterator(['a', 'b', 'c']);
        $iterator = Iterators::paddedPartition($input, 2);
        self::assertEquals(2, Iterators::size($iterator));
        $iterator->rewind();
        self::assertTrue(Iterators::elementsEqual(new ArrayIterator(['a', 'b']), Iterators::get($iterator, 0)));
        self::assertTrue(Iterators::elementsEqual(new ArrayIterator(['c', null]), Iterators::get($iterator, 1)));
    }

    /**
     * @test
     */
    public function shouldNotFindAny()
    {
        self::assertNull(Iterators::find(new EmptyIterator(), Predicates::alwaysTrue()));
        self::assertEquals('no', Iterators::find(Iterators::forArray([1, 2]), Predicates::equalTo(3), 'no'));
    }

    /**
     * @test
     */
    public function shouldNotRewindFind()
    {
        $it = Iterators::forArray([1, 2]);
        $it->next();
        self::assertEquals(2, Iterators::find($it, Predicates::notNull()));
    }

    /**
     * @test
     */
    public function shouldFind()
    {
        self::assertEquals(1, Iterators::find(Iterators::forArray([2, 1, 3]), Predicates::equalTo(1)));
    }

    /**
     * @test
     */
    public function shouldTryFind()
    {
        $result = Iterators::tryFind(Iterators::forArray([2, 1, 3]), Predicates::equalTo(1));
        self::assertTrue(Optional::of(1)->equals($result));
    }

    /**
     * @test
     */
    public function shouldTryFindReturnAbsent()
    {
        self::assertSame(Optional::absent(), Iterators::tryFind(Iterators::forArray([1, 2]), Predicates::equalTo(3)));
    }

    /**
     * @test
     */
    public function shouldReturnLast()
    {
        self::assertEquals(2, Iterators::getLast(Iterators::forArray([1, 2])));
        self::assertEquals(1, Iterators::getLast(Iterators::forArray([1])));
        self::assertNull(Iterators::getLast(new EmptyIterator()));
        self::assertEquals('default', Iterators::getLast(new EmptyIterator(), 'default'));
    }

    /**
     * @test
     */
    public function shouldReturnNext()
    {
        $it = Iterators::forArray([1, 2]);
        self::assertEquals(1, Iterators::getNext($it));
        self::assertEquals(2, Iterators::getNext($it));
        self::assertNull(Iterators::getNext($it));
        self::assertNull(Iterators::getNext($it));
        self::assertEquals('default', Iterators::getNext(new EmptyIterator(), 'default'));
    }

    /**
     * @test
     */
    public function shouldSkip()
    {
        $it = new ArrayIterator([1, 2]);
        self::assertEquals(1, Iterators::advance($it, 1));
        self::assertEquals(2, $it->current());

        $it2 = new EmptyIterator();
        self::assertEquals(0, Iterators::advance($it2, 3));
    }

    /**
     * @test
     */
    public function shouldTransform()
    {
        $iterator = new ArrayIterator([1, 2, null, 3, null]);
        $iterator->rewind();
        $iterator->next();
        $iterator->next();
        $it = Iterators::transform($iterator, function ($element) {
            return $element === null ? 0 : $element;
        });
        self::assertEquals(0, $it->current());
        $it->next();
        self::assertEquals(3, $it->current());
    }

    /**
     * @test
     */
    public function shouldCalculateSize()
    {
        $iterator = new ArrayIterator([1, 2, null, 3, null]);
        $iterator->rewind();
        $iterator->next();
        $iterator->next();
        self::assertEquals(3, Iterators::size($iterator));
    }

    /**
     * @test
     */
    public function shouldReturnToString()
    {
        $it = new ArrayIterator([1, 2, 3]);
        $it->next();
        self::assertEquals('[2, 3]', Iterators::toString($it));
    }

    /**
     * @test
     */
    public function shouldCallEachElements()
    {
        $it = new ArrayIterator([1, 2, 3]);
        $res = [];
        Iterators::each($it, function ($element) use (&$res) {
            $res[] = $element;
        });
        self::assertEquals([1, 2, 3], $res);
    }
}
