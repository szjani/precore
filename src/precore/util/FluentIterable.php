<?php
declare(strict_types=1);

namespace precore\util;

use Countable;
use Iterator;
use IteratorAggregate;
use OutOfBoundsException;
use precore\lang\BaseObject;
use Traversable;

/**
 * {@link FluentIterable} provides a flexible way to filter, transform and limit {@link IteratorAggregate}s.
 * All of it is managed in a lazy manner, thus constructing an object is cheap. All methods return a new instance.
 *
 * <pre>
 * $topAdminUserNames = FluentIterable::from($repository->getUsers())
 *   ->filter($hasAdminRoleFilter)
 *   ->transform($userNameTransformer)
 *   ->limit(10);
 * </pre>
 *
 * In the above example, $hasAdminRoleFilter is a predicate that accept a user if that is an administrator,
 * $userNameTransformer returns the name of the input user. Iterating over $topAdminUserNames results 10 user names.
 *
 * @see Predicates
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class FluentIterable extends BaseObject implements IteratorAggregate, Countable
{
    /**
     * @var IteratorAggregate
     */
    private $iterable;

    private function __construct(IteratorAggregate $iterable)
    {
        $this->iterable = $iterable;
    }

    /**
     * Creates an object from the given array.
     *
     * @param array $array
     * @return FluentIterable
     */
    public static function of(array $array) : FluentIterable
    {
        return self::from(Iterables::fromArray($array));
    }

    /**
     * Creates an object from the given {@link Traversable}.
     *
     * @param Traversable $traversable
     * @return FluentIterable
     */
    public static function from(Traversable $traversable) : FluentIterable
    {
        return new FluentIterable(Iterables::from($traversable));
    }

    /**
     * Returns a fluent iterable whose iterators traverse first the elements
     * of this fluent iterable, followed by those of other.
     *
     * @param IteratorAggregate $other
     * @return FluentIterable
     */
    public function append(IteratorAggregate $other) : FluentIterable
    {
        return self::from(Iterables::concat($this, $other));
    }

    /**
     * Returns the elements from this fluent iterable that satisfy a predicate.
     *
     * @param callable $predicate
     * @return FluentIterable
     */
    public function filter(callable $predicate) : FluentIterable
    {
        return self::from(Iterables::filter($this, $predicate));
    }

    /**
     * Returns the elements from this fluent iterable that are instances of class className.
     *
     * @param string $className
     * @return FluentIterable
     */
    public function filterBy(string $className) : FluentIterable
    {
        return self::from(Iterables::filterBy($this, $className));
    }

    /**
     * Returns true if any element in this fluent iterable satisfies the predicate.
     *
     * @param callable $predicate
     * @return boolean
     */
    public function anyMatch(callable $predicate) : bool
    {
        return Iterables::any($this, $predicate);
    }

    /**
     * Returns true if every element in this fluent iterable satisfies the predicate.
     * If this fluent iterable is empty, true is returned.
     *
     * @param callable $predicate
     * @return boolean
     */
    public function allMatch(callable $predicate) : bool
    {
        return Iterables::all($this, $predicate);
    }

    /**
     * Returns a fluent iterable that applies transformer to each element of this fluent iterable.
     *
     * @param callable $transformer
     * @return FluentIterable
     */
    public function transform(callable $transformer) : FluentIterable
    {
        return self::from(Iterables::transform($this, $transformer));
    }

    /**
     * Returns a fluent iterable that applies mapper to each element of this fluent iterable.
     *
     * @param callable $mapper
     * @return FluentIterable
     */
    public function map(callable $mapper) : FluentIterable
    {
        return $this->transform($mapper);
    }

    /**
     * Applies function to each element of this fluent iterable and returns a fluent iterable
     * with the concatenated combination of results. Transformer returns a Traversable of results.
     *
     * @param callable $transformer
     * @return FluentIterable
     * @see flatMap
     */
    public function transformAndConcat(callable $transformer) : FluentIterable
    {
        return self::from(Iterables::concatIterables($this->transform($transformer)));
    }

    /**
     * Applies function to each element of this fluent iterable and returns a fluent iterable
     * with the concatenated combination of results. Transformer returns a Traversable of results.
     *
     * @param callable $transformer
     * @return FluentIterable
     */
    public function flatMap(callable $transformer) : FluentIterable
    {
        return $this->transformAndConcat($transformer);
    }

    /**
     * Creates a fluent iterable with the first size elements of this fluent iterable.
     * If this fluent iterable does not contain that many elements,
     * the returned fluent iterable will have the same behavior as this fluent iterable.
     *
     * @param $limit
     * @return FluentIterable
     */
    public function limit($limit) : FluentIterable
    {
        return self::from(Iterables::limit($this, $limit));
    }

    /**
     * Returns a view of this fluent iterable that skips its first numberToSkip elements.
     * If this fluent iterable contains fewer than numberToSkip elements,
     * the returned fluent iterable skips all of its elements.
     *
     * @param $numberToSkip
     * @return FluentIterable
     */
    public function skip($numberToSkip) : FluentIterable
    {
        return self::from(Iterables::skip($this, $numberToSkip));
    }

    /**
     * Returns a string containing all of the elements of this fluent iterable joined with joiner.
     *
     * @param Joiner $joiner
     * @return string
     */
    public function join(Joiner $joiner) : string
    {
        return $joiner->join($this);
    }

    /**
     * Returns an Optional containing the first element in this fluent iterable.
     *
     * @return Optional
     */
    public function first() : Optional
    {
        try {
            return Optional::ofNullable($this->get(0));
        } catch (OutOfBoundsException $e) {
            return Optional::absent();
        }
    }

    /**
     * Returns an Optional containing the first element in this fluent iterable
     * that satisfies the given predicate, if such an element exists.
     *
     * @param callable $predicate
     * @return Optional
     */
    public function firstMatch(callable $predicate) : Optional
    {
        return $this->filter($predicate)->first();
    }

    /**
     * @return Optional
     */
    public function last() : Optional
    {
        return Optional::ofNullable(Iterators::getLast($this->iterator()));
    }

    /**
     * Returns the element at the specified position in this fluent iterable.
     *
     * @param $index
     * @return mixed
     * @throws OutOfBoundsException if $index is invalid
     */
    public function get($index)
    {
        return Iterables::get($this, $index);
    }

    /**
     * Determines whether this fluent iterable is empty.
     *
     * @return boolean
     */
    public function isEmpty() : bool
    {
        return Iterables::isEmpty($this);
    }

    /**
     * Returns true if this fluent iterable contains any object for which Objects::equal(element, object) is true.
     *
     * @param $element
     * @return boolean
     */
    public function contains($element) : bool
    {
        return Iterables::contains($this, $element);
    }

    /**
     * Returns the number of elements in this fluent iterable.
     *
     * @return int
     */
    public function size() : int
    {
        return Iterators::size($this->iterator());
    }

    /**
     * Returns an iterator provided by the inner {@link IteratorAggregate}.
     *
     * @return Iterator
     */
    public function iterator() : Iterator
    {
        return Iterators::from($this->iterable);
    }

    /**
     * Executes the given function on all elements.
     *
     * @param callable $function
     * @return void
     */
    public function each(callable $function) : void
    {
        Iterators::each($this->iterator(), $function);
    }

    /**
     * Sorts the elements in the order specified by comparator.
     *
     * @param Comparator $comparator
     * @return FluentIterable
     */
    public function sorted(Comparator $comparator) : FluentIterable
    {
        $array = $this->toArray();
        Arrays::sort($array, $comparator);
        return self::of($array);
    }

    /**
     * @param $size
     * @return FluentIterable
     */
    public function partition($size) : FluentIterable
    {
        return FluentIterable::from(Iterables::partition($this, $size));
    }

    /**
     * Converts this object to an array. This means it will iterate over this object.
     * It should be used only if it is reasonable.
     *
     * @return array
     */
    public function toArray() : array
    {
        $res = [];
        Iterators::each($this->iterator(), function ($element) use (&$res) {
            $res[] = $element;
        });
        return $res;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return $this->iterator();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return $this->size();
    }

    public function toString() : string
    {
        return Iterables::toString($this);
    }
}
