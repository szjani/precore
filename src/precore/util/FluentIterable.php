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

use Iterator;
use IteratorAggregate;
use OutOfBoundsException;
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
    public static function of(array $array)
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
     * @param $numberToSkip
     * @return FluentIterable
     */
    public function skip($numberToSkip)
    {
        return self::from(Iterables::skip($this->iterable, $numberToSkip));
    }

    /**
     * @param Joiner $joiner
     * @return string
     */
    public function join(Joiner $joiner)
    {
        return $joiner->join($this);
    }

    /**
     * @param $index
     * @return mixed
     * @throw OutOfBoundsException if $index is invalid
     */
    public function get($index)
    {
        return Iterables::get($this, $index);
    }

    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return Iterables::isEmpty($this);
    }

    /**
     * @param $element
     * @return boolean
     */
    public function contains($element)
    {
        return Iterables::contains($this, $element);
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

    public function __toString()
    {
        return '[' . Joiner::on(', ')->useForNull('null')->join($this) . ']';
    }
}
