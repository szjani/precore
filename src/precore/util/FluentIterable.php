<?php

namespace precore\util;

use Iterator;
use IteratorAggregate;
use Traversable;

/**
 * {@link FluentIterable} provides a flexible way to filter, transform and limit {@link IteratorAggregate}s.
 * All of it is managed in a lazy manner, thus constructing an object is cheap. All methods return a new instance.
 *
 * <pre>
 * $topAdminUserNames = FluentIterable::from(repository.getUsers())
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
final class FluentIterable implements IteratorAggregate
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
    public static function fromArray(array $array)
    {
        return self::from(Iterables::fromArray($array));
    }

    /**
     * Creates an object from the given {@link Traversable}.
     *
     * @param Traversable $traversable
     * @return FluentIterable
     */
    public static function from(Traversable $traversable)
    {
        return ($traversable instanceof IteratorAggregate)
            ? new FluentIterable($traversable)
            : new FluentIterable(Iterables::from($traversable));
    }

    /**
     * Only those objects will be visible when iterating over this object, which are accepted by the given predicate.
     *
     * @param callable $predicate
     * @return FluentIterable
     */
    public function filter(callable $predicate)
    {
        return self::from(Iterables::filter($this->iterable, $predicate));
    }

    /**
     * All objects produced by this object will be converted by the given transformer.
     *
     * @param callable $transformer
     * @return FluentIterable
     */
    public function transform(callable $transformer)
    {
        return self::from(Iterables::transform($this->iterable, $transformer));
    }

    /**
     * Only the first $limit item can be visible.
     *
     * @param $limit
     * @return FluentIterable
     */
    public function limit($limit)
    {
        return self::from(Iterables::limit($this->iterable, $limit));
    }

    /**
     * Returns an iterator provided by the inner {@link IteratorAggregate}.
     *
     * @return Iterator
     */
    public function iterator()
    {
        return $this->iterable->getIterator();
    }

    /**
     * Converts this object to an array. This means it will iterate over this object.
     * It should be used only if it is reasonable.
     *
     * @return array
     */
    public function toArray()
    {
        return iterator_to_array($this->iterator(), false);
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
}
