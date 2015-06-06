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

use CallbackFilterIterator;
use Countable;
use Iterator;
use IteratorAggregate;
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
        return Iterables::from(Iterators::forArray($array));
    }

    /**
     * Creates an {@link IteratorAggregate} from traversable, regardless of its type.
     *
     * @param Traversable $traversable
     * @return IteratorAggregate
     */
    public static function from(Traversable $traversable)
    {
        Preconditions::checkArgument($traversable instanceof Iterator || $traversable instanceof IteratorAggregate);
        return $traversable instanceof IteratorAggregate
            ? $traversable
            : new CallableIterable(
                function () use ($traversable) {
                    return $traversable;
                }
            );
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
        return new CallableIterable(
            function () use ($unfiltered, $predicate) {
                return Iterators::filter(Iterators::from($unfiltered->getIterator()), $predicate);
            }
        );
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
        return self::from(Iterators::filterBy(Iterators::from($unfiltered), $className));
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
        return self::from(Iterators::concat(Iterators::from($a), Iterators::from($b)));
    }

    /**
     * Returns the first element in iterable that satisfies the given predicate. If there is no such an element,
     * it returns $defaultValue.
     *
     * @param IteratorAggregate $iterable
     * @param callable $predicate
     * @param null $defaultValue
     * @return mixed
     */
    public static function find(IteratorAggregate $iterable, callable $predicate, $defaultValue = null)
    {
        return Iterators::find(Iterators::from($iterable), $predicate, $defaultValue);
    }

    /**
     * Divides an iterable into unmodifiable sublists of the given size (the final iterable may be smaller).
     *
     * For example, partitioning an iterable containing [a, b, c, d, e] with a partition size
     * of 3 yields [[a, b, c], [d, e]] -- an outer iterable containing two inner lists of three and two elements,
     * all in the original order.
     *
     * @param IteratorAggregate $iterable
     * @param int $size
     * @return IteratorAggregate
     */
    public static function partition(IteratorAggregate $iterable, $size)
    {
        return FluentIterable::from(Iterators::partition(Iterators::from($iterable), $size))
            ->transform(
                function (Iterator $element) {
                    return new CallableIterable(
                        function () use ($element) {
                            return $element;
                        }
                    );
                }
            );
    }

    /**
     * Divides an iterable into unmodifiable sublists of the given size,
     * padding the final iterable with null values if necessary.
     *
     * For example, partitioning an iterable containing [a, b, c, d, e] with a partition size
     * of 3 yields [[a, b, c], [d, e, null]] -- an outer iterable containing two inner lists of three elements each,
     * all in the original order.
     *
     * @param IteratorAggregate $iterable
     * @param int $size
     * @return IteratorAggregate
     */
    public static function paddedPartition(IteratorAggregate $iterable, $size)
    {
        return FluentIterable::from(Iterators::paddedPartition(Iterators::from($iterable), $size))
            ->transform(
                function (Iterator $element) {
                    return new CallableIterable(
                        function () use ($element) {
                            return $element;
                        }
                    );
                }
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
                            return Iterators::from($element);
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
        return Iterators::any(Iterators::from($iterable), $predicate);
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
        return Iterators::all(Iterators::from($iterable), $predicate);
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
        return new CallableIterable(
            function () use ($fromIterable, $transformer) {
                return Iterators::transform(Iterators::from($fromIterable), $transformer);
            }
        );
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
        return new CallableIterable(
            function () use ($iterable, $limitSize) {
                return Iterators::limit(Iterators::from($iterable->getIterator()), $limitSize);
            }
        );
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
        return Iterators::get(Iterators::from($iterable), $position);
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
        return new CallableIterable(
            function () use ($iterable, $numberToSkip) {
                $iterator = Iterators::from($iterable);
                Iterators::advance($iterator, $numberToSkip);
                return $iterator;
            }
        );
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
        return Iterators::size(Iterators::from($iterable->getIterator()));
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
        return Iterators::contains(Iterators::from($iterable), $element);
    }

    /**
     * Determines if the given iterable contains no elements.
     *
     * @param IteratorAggregate $iterable
     * @return boolean
     */
    public static function isEmpty(IteratorAggregate $iterable)
    {
        return Iterators::isEmpty(Iterators::from($iterable));
    }

    /**
     * Checks whether the elements provided by the given iterables are equal correspondingly.
     * It uses {@link Objects::equal()} for equality check.
     *
     * @param IteratorAggregate $iterable1
     * @param IteratorAggregate $iterable2
     * @return bool
     */
    public static function elementsEqual(IteratorAggregate $iterable1, IteratorAggregate $iterable2)
    {
        return Iterators::elementsEqual(Iterators::from($iterable1), Iterators::from($iterable2));
    }

    /**
     * Returns a string representation of iterable, with the format [e1, e2, ..., en].
     *
     * @param IteratorAggregate $iterable
     * @return string
     */
    public static function toString(IteratorAggregate $iterable)
    {
        return Iterators::toString(Iterators::from($iterable));
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class CallableIterable implements IteratorAggregate
{
    private $callable;

    /**
     * CallableIterable constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function getIterator()
    {
        return call_user_func($this->callable);
    }
}
