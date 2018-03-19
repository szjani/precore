<?php
declare(strict_types=1);

namespace precore\util;

use ArrayIterator;
use EmptyIterator;
use Iterator;
use IteratorAggregate;
use OuterIterator;
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
     * Returns an iterator for the given traversable.
     *
     * @param Traversable $traversable
     * @return Iterator
     */
    public static function from(Traversable $traversable) : Iterator
    {
        Preconditions::checkArgument($traversable instanceof Iterator || $traversable instanceof IteratorAggregate);
        return $traversable instanceof Iterator
            ? $traversable
            : Iterators::from($traversable->getIterator());
    }

    /**
     * Returns an iterator containing the elements of array in order.
     *
     * @param array $array
     * @return Iterator
     */
    public static function forArray(array $array) : Iterator
    {
        return new ArrayIterator($array);
    }

    /**
     * Returns an iterator containing only value.
     *
     * @param mixed $value
     * @return Iterator
     */
    public static function singletonIterator($value) : Iterator
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
    public static function filter(Iterator $unfiltered, callable $predicate) : Iterator
    {
        return new FilterIterator($unfiltered, $predicate);
    }

    /**
     * Returns all instances of class type in unfiltered.
     * The returned iterator has elements whose class is type or a subclass of type.
     *
     * @param Iterator $unfiltered
     * @param string $className
     * @return Iterator
     */
    public static function filterBy(Iterator $unfiltered, string $className) : Iterator
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
    public static function concat(Iterator $a, Iterator $b) : Iterator
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
    public static function concatIterators(Iterator $inputs) : Iterator
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
    public static function any(Iterator $iterator, callable $predicate) : bool
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
    public static function all(Iterator $iterator, callable $predicate) : bool
    {
        while ($iterator->valid()) {
            if (!Predicates::call($predicate, $iterator->current())) {
                return false;
            }
            $iterator->next();
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
    public static function indexOf(Iterator $iterator, callable $predicate) : int
    {
        $i = 0;
        while ($iterator->valid()) {
            if (Predicates::call($predicate, $iterator->current())) {
                return $i;
            }
            $i++;
            $iterator->next();
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
    public static function transform(Iterator $fromIterator, callable $transformer) : Iterator
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
    public static function limit(Iterator $iterator, int $limitSize) : Iterator
    {
        Preconditions::checkArgument(0 <= $limitSize);
        return new NoRewindNecessaryLimitIterator($iterator, $limitSize);
    }

    /**
     * @param Iterator $iterator
     * @param $numberToSkip
     * @return integer
     * @throws \InvalidArgumentException if $numberToSkip is < 0
     */
    public static function advance(Iterator $iterator, int $numberToSkip) : int
    {
        Preconditions::checkArgument(0 <= $numberToSkip);
        for ($i = 0; $i < $numberToSkip && $iterator->valid(); $i++) {
            $iterator->next();
        }
        return $i;
    }

    /**
     * Advances iterator position times, returning the element at the positionth position.
     *
     * @param Iterator $iterator
     * @param int $position
     * @return mixed
     * @throws OutOfBoundsException if position does not exist
     */
    public static function get(Iterator $iterator, int $position)
    {
        Iterators::advance($iterator, $position);
        if (!$iterator->valid()) {
            throw new OutOfBoundsException("The requested index '{$position}' is invalid");
        }
        return $iterator->current();
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
        Iterators::each($iterator, function () use (&$result) {
            $result++;
        });
        return $result;
    }

    /**
     * @param Iterator $iterator
     * @param callable $function
     * @return void
     */
    public static function each(Iterator $iterator, callable $function)
    {
        while ($iterator->valid()) {
            Functions::call($function, $iterator->current());
            $iterator->next();
        }
    }

    /**
     * Returns true if iterator contains element. The check based on {@link Objects::equal()}.
     *
     * @param Iterator $iterator
     * @param $element
     * @return boolean
     */
    public static function contains(Iterator $iterator, $element) : bool
    {
        while ($iterator->valid()) {
            if (Objects::equal($iterator->current(), $element)) {
                return true;
            }
            $iterator->next();
        }
        return false;
    }

    /**
     * Returns the number of elements in the specified iterator that equal the specified object.
     * The check based on {@link Objects::equal()}.
     *
     * @param Iterator $iterator
     * @param $element
     * @return int
     */
    public static function frequency(Iterator $iterator, $element) : int
    {
        $frequency = 0;
        Iterators::each($iterator, function ($item) use (&$frequency, $element) {
            if (Objects::equal($element, $item)) {
                $frequency++;
            }
        });
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
    public static function partition(Iterator $iterator, int $size) : Iterator
    {
        Preconditions::checkArgument(0 < $size);
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
    public static function paddedPartition(Iterator $iterator, int $size) : Iterator
    {
        Preconditions::checkArgument(0 < $size);
        return new PartitionIterator($iterator, $size, true);
    }

    /**
     * Returns the first element in iterator that satisfies the given predicate. If there is no such an element,
     * it returns $defaultValue.
     *
     * @param Iterator $iterator
     * @param callable $predicate
     * @param null $defaultValue
     * @return mixed
     */
    public static function find(Iterator $iterator, callable $predicate, $defaultValue = null)
    {
        $result = $defaultValue;
        while ($iterator->valid()) {
            $current = $iterator->current();
            if (Predicates::call($predicate, $current)) {
                $result = $current;
                break;
            }
            $iterator->next();
        }
        return $result;
    }

    /**
     * Returns an Optional containing the first element in iterator that satisfies the given predicate,
     * if such an element exists. If no such element is found, an empty Optional will be returned from this method.
     *
     * @param Iterator $iterator
     * @param callable $predicate
     * @return Optional
     */
    public static function tryFind(Iterator $iterator, callable $predicate) : Optional
    {
        return Optional::ofNullable(self::find($iterator, $predicate));
    }

    /**
     * @param Iterator $iterator
     * @return boolean
     */
    public static function isEmpty(Iterator $iterator) : bool
    {
        return !$iterator->valid();
    }

    /**
     * Returns the current element of the iterator if it's valid and calls {@link Iterator::next} on it.
     * If the iterator is not valid, it returns $defaultValue.
     *
     * @param Iterator $iterator
     * @param null $defaultValue
     * @return mixed|null
     */
    public static function getNext(Iterator $iterator, $defaultValue = null)
    {
        $result = $defaultValue;
        if ($iterator->valid()) {
            $result = $iterator->current();
            $iterator->next();
        }
        return $result;
    }

    /**
     * Returns the last element provided by $iterator. If the iterator is not valid, returns $defaultValue.
     *
     * @param Iterator $iterator
     * @param null $defaultValue
     * @return mixed|null
     */
    public static function getLast(Iterator $iterator, $defaultValue = null)
    {
        $last = $defaultValue;
        Iterators::each($iterator, function ($element) use (&$last) {
            $last = $element;
        });
        return $last;
    }

    /**
     * Checks whether the elements provided by the given iterators are equal correspondingly.
     * It uses {@link Objects::equal()} for equality check.
     *
     * @param Iterator $iterator1
     * @param Iterator $iterator2
     * @return bool
     */
    public static function elementsEqual(Iterator $iterator1, Iterator $iterator2) : bool
    {
        while ($iterator1->valid() && $iterator2->valid()) {
            if (!Objects::equal($iterator1->current(), $iterator2->current())) {
                return false;
            }
            $iterator1->next();
            $iterator2->next();
        }
        return !$iterator1->valid() && !$iterator2->valid();
    }

    /**
     * Returns a string representation of iterator, with the format [e1, e2, ..., en].
     *
     * @param Iterator $iterator
     * @return string
     */
    public static function toString(Iterator $iterator) : string
    {
        return '[' . Collections::standardJoiner()->join($iterator) . ']';
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class NoRewindNecessaryIterator implements OuterIterator
{
    /**
     * @var Iterator
     */
    private $iterator;

    /**
     * FilterIterator constructor.
     * @param Iterator $iterator
     */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function current()
    {
        return $this->iterator->current();
    }

    public function next()
    {
        $this->iterator->next();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function rewind()
    {
        $this->iterator->rewind();
    }

    public function getInnerIterator()
    {
        return $this->iterator;
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class FilterIterator extends NoRewindNecessaryIterator
{
    /**
     * @var callable
     */
    private $filter;
    private $key = 0;

    /**
     * FilterIterator constructor.
     * @param Iterator $iterator
     * @param callable $filter
     */
    public function __construct(Iterator $iterator, callable $filter)
    {
        parent::__construct($iterator);
        $this->filter = $filter;
    }

    public function rewind()
    {
        parent::rewind();
        $this->key = 0;
    }

    public function key()
    {
        return $this->key;
    }

    public function current()
    {
        $this->findNext();
        return parent::current();
    }

    public function next()
    {
        parent::next();
        $this->findNext();
        $this->key++;
    }

    public function valid()
    {
        $this->findNext();
        return parent::valid();
    }


    private function findNext()
    {
        while (parent::valid() && !Predicates::call($this->filter, parent::current())) {
            parent::next();
        }
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class TransformerIterator extends NoRewindNecessaryIterator
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
        return Functions::call($this->transformer, parent::current());
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ConcatIterator extends NoRewindNecessaryIterator
{
    /**
     * @var Iterator
     */
    private $current;
    private $key = 0;

    public function __construct(Iterator $iterator)
    {
        parent::__construct($iterator);
        $this->current = new EmptyIterator();
    }

    public function rewind()
    {
        parent::rewind();
        $this->current = new EmptyIterator();
        $this->key = 0;
    }

    public function current()
    {
        if (!$this->current->valid()) {
            $this->findNextIterator();
        }
        return $this->current->current();
    }

    public function valid()
    {
        if (!$this->current->valid()) {
            $this->findNextIterator();
        }
        return $this->current->valid();
    }

    public function next()
    {
        $this->current->next();
        if (!$this->current->valid()) {
            $this->findNextIterator();
        }
        $this->key++;
    }

    public function key()
    {
        return $this->key;
    }

    private function findNextIterator()
    {
        while (!$this->current->valid() && parent::valid()) {
            $this->current = parent::current();
            parent::next();
        }
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class NoRewindNecessaryLimitIterator extends NoRewindNecessaryIterator
{
    /**
     * @var int
     */
    private $limit;

    private $count = 0;

    public function __construct(Iterator $iterator, $limit)
    {
        parent::__construct($iterator);
        $this->limit = $limit;
    }

    public function rewind()
    {
        parent::rewind();
        $this->count = 0;
    }

    public function valid()
    {
        return $this->count < $this->limit && parent::valid();
    }

    public function next()
    {
        parent::next();
        $this->count++;
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class PartitionIterator extends NoRewindNecessaryIterator
{
    private $size;

    /**
     * @var Iterator
     */
    private $currentIterator;

    /**
     * @var bool
     */
    private $padded;

    private $key = 0;

    /**
     * @param Iterator $iterator
     * @param int $size
     * @param bool $padded
     */
    public function __construct(Iterator $iterator, $size, $padded = false)
    {
        parent::__construct($iterator);
        $this->size = $size;
        $this->padded = $padded;
    }

    public function current()
    {
        if ($this->currentIterator === null) {
            $this->findNext();
        }
        return $this->currentIterator;
    }

    public function next()
    {
        $this->findNext();
        $this->key++;
    }

    public function key()
    {
        return $this->key;
    }

    public function valid()
    {
        if ($this->currentIterator === null) {
            $this->findNext();
        }
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
        return $this->currentIterator;
    }
}
