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
use CallbackFilterIterator;
use Countable;
use Iterator;
use IteratorIterator;
use LimitIterator;
use MultipleIterator;
use OutOfBoundsException;
use Traversable;

/**
 * Helper class for {@link Iterator} objects.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Iterators
{
    private function __construct()
    {
    }

    /**
     * Returns an iterator containing the elements of array in order.
     *
     * @param array $array
     * @return Iterator
     */
    public static function forArray(array $array)
    {
        return new ArrayIterator($array);
    }

    /**
     * Returns an iterator containing only value.
     *
     * @param mixed $value
     * @return Iterator
     */
    public static function singletonIterator($value)
    {
        return self::forArray([$value]);
    }

    /**
     * Returns the elements of unfiltered that satisfy a predicate.
     *
     * @param Iterator $unfiltered
     * @param callable $predicate
     * @return Iterator
     */
    public static function filter(Iterator $unfiltered, callable $predicate)
    {
        return new CallbackFilterIterator($unfiltered, $predicate);
    }

    /**
     * Returns all instances of class type in unfiltered.
     * The returned iterator has elements whose class is type or a subclass of type.
     *
     * @param Iterator $unfiltered
     * @param string $className
     * @return Iterator
     */
    public static function filterBy(Iterator $unfiltered, $className)
    {
        return self::filter($unfiltered, Predicates::instance($className));
    }

    /**
     * Combines two iterators into a single iterator. The returned iterator iterates across the elements in a,
     * followed by the elements in b. The source iterators are not polled until necessary.
     *
     * @param Iterator $a
     * @param Iterator $b
     * @return Iterator
     */
    public static function concat(Iterator $a, Iterator $b)
    {
        return self::concatIterators(new ArrayIterator([$a, $b]));
    }

    /**
     * Combines multiple iterators into a single iterator. The returned iterator iterates across
     * the elements of each iterator in inputs. The input iterators are not polled until necessary.
     *
     * @param Iterator $inputs
     * @return Iterator
     */
    public static function concatIterators(Iterator $inputs)
    {
        return new ConcatIterator($inputs);
    }

    /**
     * Returns true if one or more elements returned by iterator satisfy the given predicate.
     *
     * @param Iterator $iterator
     * @param callable $predicate
     * @return boolean
     */
    public static function any(Iterator $iterator, callable $predicate)
    {
        return self::indexOf($iterator, $predicate) !== -1;
    }

    /**
     * Returns true if every element returned by iterator satisfies the given predicate.
     * If iterator is empty, true is returned.
     *
     * @param Iterator $iterator
     * @param callable $predicate
     * @return boolean
     */
    public static function all(Iterator $iterator, callable $predicate)
    {
        foreach ($iterator as $element) {
            if (!Predicates::call($predicate, $element)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns the index in iterator of the first element that satisfies the provided predicate,
     * or -1 if the Iterator has no such elements.
     *
     * @param Iterator $iterator
     * @param callable $predicate
     * @return int
     */
    public static function indexOf(Iterator $iterator, callable $predicate)
    {
        $i = 0;
        foreach ($iterator as $element) {
            if (Predicates::call($predicate, $element)) {
                return $i;
            }
            $i++;
        }
        return -1;
    }

    /**
     * Returns an iterator that applies transformer to each element of fromIterator.
     *
     * @param Iterator $fromIterator
     * @param callable $transformer
     * @return Iterator
     */
    public static function transform(Iterator $fromIterator, callable $transformer)
    {
        return new TransformerIterator($fromIterator, $transformer);
    }

    /**
     * Creates an iterator returning the first limitSize elements of the given iterator.
     * If the original iterator does not contain that many elements,
     * the returned iterator will have the same behavior as the original iterator.
     *
     * @param Iterator $iterator
     * @param int $limitSize
     * @return Iterator
     * @throws \InvalidArgumentException if $limit is negative
     */
    public static function limit(Iterator $iterator, $limitSize)
    {
        Preconditions::checkArgument(is_int($limitSize) && 0 <= $limitSize);
        return new LimitIterator($iterator, 0, $limitSize);
    }

    /**
     * @param Iterator $iterator
     * @param $numberToSkip
     * @return Iterator
     */
    public static function skip(Iterator $iterator, $numberToSkip)
    {
        return new LimitIterator($iterator, $numberToSkip);
    }

    /**
     * Advances iterator position + 1 times, returning the element at the positionth position.
     *
     * @param Iterator $iterator
     * @param $position
     * @return mixed
     * @throws OutOfBoundsException if position does not exist
     */
    public static function get(Iterator $iterator, $position)
    {
        foreach (self::limit(self::skip($iterator, $position), 1) as $element) {
            return $element;
        }
        throw new OutOfBoundsException("The requested index '{$position}' is invalid");
    }

    /**
     * Returns the number of elements in iterator.
     *
     * @param Iterator $iterator
     * @return int
     */
    public static function size(Iterator $iterator)
    {
        $result = 0;
        if ($iterator instanceof Countable) {
            $result = $iterator->count();
        } else {
            foreach ($iterator as $item) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * Returns true if iterator contains element.
     *
     * @param Iterator $iterator
     * @param $element
     * @return boolean
     */
    public static function contains(Iterator $iterator, $element)
    {
        foreach ($iterator as $item) {
            if (Objects::equal($item, $element)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the number of elements in the specified iterator that equal the specified object.
     *
     * @param Iterator $iterator
     * @param $element
     * @return int
     */
    public static function frequency(Iterator $iterator, $element)
    {
        $frequency = 0;
        foreach ($iterator as $item) {
            if (Objects::equal($item, $element)) {
                $frequency++;
            }
        }
        return $frequency;
    }

    /**
     * Divides an iterator into unmodifiable sublists of the given size (the final list may be smaller).
     *
     * For example, partitioning an iterator containing [a, b, c, d, e] with a partition size
     * of 3 yields [[a, b, c], [d, e]] -- an outer iterator containing two inner lists of three and two elements,
     * all in the original order.
     *
     * @param Iterator $iterator
     * @param int $size
     * @return Iterator
     * @throws \InvalidArgumentException if $limit is non positive
     */
    public static function partition(Iterator $iterator, $size)
    {
        Preconditions::checkArgument(is_int($size) && 0 < $size);
        return new PartitionIterator($iterator, $size);
    }

    /**
     * Divides an iterator into unmodifiable sublists of the given size,
     * padding the final iterator with null values if necessary.
     *
     * For example, partitioning an iterator containing [a, b, c, d, e] with a partition size
     * of 3 yields [[a, b, c], [d, e, null]] -- an outer iterator containing two inner lists of three elements each,
     * all in the original order.
     *
     * @param Iterator $iterator
     * @param int $size
     * @return Iterator
     * @throws \InvalidArgumentException if $limit is non positive
     */
    public static function paddedPartition(Iterator $iterator, $size)
    {
        Preconditions::checkArgument(is_int($size) && 0 < $size);
        return new PartitionIterator($iterator, $size, true);
    }

    /**
     * Returns the first element in iterator that satisfies the given predicate.
     *
     * @param Iterator $iterator
     * @param callable $predicate
     * @param null $defaultValue
     * @return mixed
     */
    public static function find(Iterator $iterator, callable $predicate, $defaultValue = null)
    {
        $result = $defaultValue;
        foreach ($iterator as $element) {
            if (Predicates::call($predicate, $element)) {
                $result = $element;
                break;
            }
        }
        return $result;
    }

    /**
     * @param Iterator $iterator
     * @return boolean
     */
    public static function isEmpty(Iterator $iterator)
    {
        $iterator->rewind();
        return !$iterator->valid();
    }

    /**
     * Checks whether the elements provided by the given iterators are equal correspondingly.
     * It uses {@link Objects::equal()} for equality check.
     *
     * @param Iterator $iterator1
     * @param Iterator $iterator2
     * @return bool
     */
    public static function elementsEqual(Iterator $iterator1, Iterator $iterator2)
    {
        $iterator1->rewind();
        $iterator2->rewind();
        while ($iterator1->valid() && $iterator2->valid()) {
            if (!Objects::equal($iterator1->current(), $iterator2->current())) {
                return false;
            }
            $iterator1->next();
            $iterator2->next();
        }
        return !$iterator1->valid() && !$iterator2->valid();
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class TransformerIterator extends IteratorIterator
{
    /**
     * @var callable
     */
    private $transformer;

    /**
     * @param Iterator $iterator
     * @param callable $transformer
     */
    public function __construct(Iterator $iterator, callable $transformer)
    {
        parent::__construct($iterator);
        $this->transformer = $transformer;
    }

    public function current()
    {
        return call_user_func($this->transformer, parent::current());
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ConcatIterator extends IteratorIterator
{
    public function current()
    {
        return $this->getInnerIterator()->current()->current();
    }

    public function valid()
    {
        return $this->getInnerIterator()->valid() && $this->getInnerIterator()->current()->valid();
    }

    public function next()
    {
        $iterator = $this->getInnerIterator();
        while (true) {
            if ($iterator->valid()) {
                $iterator->current()->next();
                if ($iterator->current()->valid()) {
                    break;
                } else {
                    $iterator->next();
                    if ($iterator->valid()) {
                        $iterator->current()->rewind();
                        if ($iterator->current()->valid()) {
                            break;
                        }
                    }
                }
            } else {
                break;
            }
        }
    }

    public function rewind()
    {
        $iterator = $this->getInnerIterator();
        $iterator->rewind();
        if ($iterator->current() !== null) {
            $iterator->current()->rewind();
        }
        if (!$this->valid()) {
            $this->next();
        }
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class PartitionIterator extends IteratorIterator
{
    private $size;
    private $currentIterator;
    /**
     * @var bool
     */
    private $padded;

    /**
     * @param Traversable $iterator
     * @param int $size
     * @param bool $padded
     */
    public function __construct(Traversable $iterator, $size, $padded = false)
    {
        parent::__construct($iterator);
        $this->size = $size;
        $this->padded = $padded;
    }

    public function current()
    {
        return $this->currentIterator;
    }

    public function rewind()
    {
        parent::rewind();
        $this->findNext();
    }

    public function next()
    {
        $this->findNext();
    }

    public function valid()
    {
        return $this->currentIterator !== null;
    }

    private function findNext()
    {
        $array = [];
        for ($i = 0; $i < $this->size && $this->getInnerIterator()->valid(); $this->getInnerIterator()->next(), $i++) {
            $array[] = $this->getInnerIterator()->current();
        }
        if ($this->padded) {
            $array = array_pad($array, $this->size, null);
        }
        $this->currentIterator = 0 < $i
            ? new ArrayIterator($array)
            : null;
    }
}
