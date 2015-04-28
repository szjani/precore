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
use CallbackFilterIterator;
use Countable;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use IteratorIterator;
use Traversable;

/**
 * Helper class for {@link IteratorAggregate} objects.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Iterables
{
    private function __construct()
    {
    }

    /**
     * @param array $array
     * @return IteratorAggregate
     */
    public static function fromArray(array $array)
    {
        return new ArrayObject($array);
    }

    /**
     * @param Traversable $traversable
     * @return IteratorAggregate
     */
    public static function from(Traversable $traversable)
    {
        return new FixIterable($traversable);
    }

    /**
     * Returns the elements of unfiltered that satisfy a predicate.
     *
     * @param IteratorAggregate $unfiltered
     * @param callable $predicate
     * @return IteratorAggregate
     */
    public static function filter(IteratorAggregate $unfiltered, callable $predicate)
    {
        return new CallbackFilterIterable($unfiltered, $predicate);
    }

    /**
     * Returns all instances of class className in unfiltered.
     *
     * @param IteratorAggregate $unfiltered
     * @param string $className
     * @return IteratorAggregate
     */
    public static function filterBy(IteratorAggregate $unfiltered, $className)
    {
        return self::from(Iterators::filterBy(new IteratorIterator($unfiltered), $className));
    }

    /**
     * Combines two iterables into a single iterable.
     * The returned iterable has an iterator that traverses the elements in a,
     * followed by the elements in b. The source iterators are not polled until necessary.
     *
     * @param IteratorAggregate $a
     * @param IteratorAggregate $b
     * @return IteratorAggregate
     */
    public static function concat(IteratorAggregate $a, IteratorAggregate $b)
    {
        return self::from(
            Iterators::concat(
                new IteratorIterator($a),
                new IteratorIterator($b)
            )
        );
    }

    /**
     * Combines multiple iterables into a single iterable.
     * The returned iterable has an iterator that traverses the elements of each iterable in inputs.
     * The input iterators are not polled until necessary.
     *
     * @param IteratorAggregate $iterables of Traversable objects
     * @return IteratorAggregate
     */
    public static function concatIterables(IteratorAggregate $iterables)
    {
        return self::from(
            Iterators::concatIterators(
                FluentIterable::from($iterables)
                    ->transform(
                        function (Traversable $element) {
                            return new IteratorIterator($element);
                        }
                    )
                    ->iterator()
            )
        );
    }

    /**
     * Returns true if any element in iterable satisfies the predicate.
     *
     * @param IteratorAggregate $iterable
     * @param callable $predicate
     * @return boolean
     */
    public static function any(IteratorAggregate $iterable, callable $predicate)
    {
        return Iterators::any(new IteratorIterator($iterable), $predicate);
    }

    /**
     * Returns true if every element in iterable satisfies the predicate. If iterable is empty, true is returned.
     *
     * @param IteratorAggregate $iterable
     * @param callable $predicate
     * @return boolean
     */
    public static function all(IteratorAggregate $iterable, callable $predicate)
    {
        return Iterators::all(new IteratorIterator($iterable), $predicate);
    }

    /**
     * Returns an iterable that applies function to each element of fromIterable.
     *
     * @param IteratorAggregate $fromIterable
     * @param callable $transformer
     * @return IteratorAggregate
     */
    public static function transform(IteratorAggregate $fromIterable, callable $transformer)
    {
        return new TransformerIterable($fromIterable, $transformer);
    }

    /**
     * Creates an iterable with the first limitSize elements of the given iterable.
     * If the original iterable does not contain that many elements,
     * the returned iterable will have the same behavior as the original iterable.
     *
     * @param IteratorAggregate $iterable
     * @param $limitSize
     * @return IteratorAggregate
     */
    public static function limit(IteratorAggregate $iterable, $limitSize)
    {
        return new LimitIterable($iterable, $limitSize);
    }

    /**
     * Returns the element at the specified position in an iterable.
     *
     * @param IteratorAggregate $iterable
     * @param $position
     * @return mixed
     * @throws \OutOfBoundsException if position does not exist
     */
    public static function get(IteratorAggregate $iterable, $position)
    {
        return Iterators::get(new IteratorIterator($iterable), $position);
    }

    /**
     * Returns a view of iterable that skips its first numberToSkip elements.
     * If iterable contains fewer than numberToSkip elements,
     * the returned iterable skips all of its elements.
     *
     * @param IteratorAggregate $iterable
     * @param $numberToSkip
     * @return IteratorAggregate
     */
    public static function skip(IteratorAggregate $iterable, $numberToSkip)
    {
        return new SkipIterable($iterable, $numberToSkip);
    }

    /**
     * Returns the number of elements in iterable.
     *
     * @param IteratorAggregate $iterable
     * @return int
     */
    public static function size(IteratorAggregate $iterable)
    {
        if ($iterable instanceof Countable) {
            return $iterable->count();
        }
        $traversable = $iterable->getIterator();
        if ($traversable instanceof Iterator) {
            return Iterators::size($traversable);
        } elseif ($traversable instanceof IteratorAggregate) {
            return self::size($traversable);
        }
        throw new InvalidArgumentException('Not supported built-in class');
    }

    /**
     * Returns true if iterable contains any object for which Objects::equal(element, object) is true.
     *
     * @param IteratorAggregate $iterable
     * @param $element
     * @return boolean
     */
    public static function contains(IteratorAggregate $iterable, $element)
    {
        return Iterators::contains(new IteratorIterator($iterable), $element);
    }

    /**
     * Determines if the given iterable contains no elements.
     *
     * @param IteratorAggregate $iterable
     * @return boolean
     */
    public static function isEmpty(IteratorAggregate $iterable)
    {
        return Iterators::isEmpty(new IteratorIterator($iterable));
    }

    /**
     * @param IteratorAggregate $iterable1
     * @param IteratorAggregate $iterable2
     * @return bool
     */
    public static function elementsEqual(IteratorAggregate $iterable1, IteratorAggregate $iterable2)
    {
        return Iterators::elementsEqual(
            new IteratorIterator($iterable1),
            new IteratorIterator($iterable2)
        );
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class FixIterable implements IteratorAggregate
{
    /**
     * @var Traversable
     */
    private $iterator;

    /**
     * @param Traversable $traversable
     */
    public function __construct(Traversable $traversable)
    {
        $this->iterator = $traversable;
    }

    public function getIterator()
    {
        return new IteratorIterator($this->iterator);
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class TransformerIterable implements IteratorAggregate
{
    private $callable;
    private $iterable;

    /**
     * @param IteratorAggregate $iterable
     * @param callable $callable
     */
    public function __construct(IteratorAggregate $iterable, callable $callable)
    {
        $this->callable = $callable;
        $this->iterable = $iterable;
    }

    public function getIterator()
    {
        return Iterators::transform(new IteratorIterator($this->iterable->getIterator()), $this->callable);
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class LimitIterable implements IteratorAggregate
{
    private $limit;

    /**
     * @var IteratorAggregate
     */
    private $iterable;

    /**
     * LimitIterable constructor.
     * @param IteratorAggregate $iterable
     * @param $limit
     */
    public function __construct(IteratorAggregate $iterable, $limit)
    {
        $this->limit = $limit;
        $this->iterable = $iterable;
    }

    public function getIterator()
    {
        return Iterators::limit(new IteratorIterator($this->iterable->getIterator()), $this->limit);
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class SkipIterable implements IteratorAggregate
{
    private $skip;

    /**
     * @var IteratorAggregate
     */
    private $iterable;

    /**
     * LimitIterable constructor.
     * @param IteratorAggregate $iterable
     * @param $skip
     */
    public function __construct(IteratorAggregate $iterable, $skip)
    {
        $this->skip = $skip;
        $this->iterable = $iterable;
    }

    public function getIterator()
    {
        return Iterators::skip(new IteratorIterator($this->iterable->getIterator()), $this->skip);
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class CallbackFilterIterable implements IteratorAggregate
{
    /**
     * @var IteratorAggregate
     */
    private $iterable;

    /**
     * @var callable
     */
    private $predicate;

    /**
     * @param IteratorAggregate $iterable
     * @param callable $predicate
     */
    public function __construct(IteratorAggregate $iterable, callable $predicate)
    {
        $this->iterable = $iterable;
        $this->predicate = $predicate;
    }

    /**
     * @return CallbackFilterIterator
     */
    public function getIterator()
    {
        return Iterators::filter(new IteratorIterator($this->iterable->getIterator()), $this->predicate);
    }
}
