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
     * @param IteratorAggregate $iterable
     * @param callable $predicate
     * @return IteratorAggregate
     */
    public static function filter(IteratorAggregate $iterable, callable $predicate)
    {
        return new CallbackFilterIterable($iterable, $predicate);
    }

    /**
     * @param IteratorAggregate $iterable
     * @param callable $transformer
     * @return IteratorAggregate
     */
    public static function transform(IteratorAggregate $iterable, callable $transformer)
    {
        return new TransformerIterable($iterable, $transformer);
    }

    /**
     * @param IteratorAggregate $iterable
     * @param $limit
     * @return IteratorAggregate
     */
    public static function limit(IteratorAggregate $iterable, $limit)
    {
        return new LimitIterable($iterable, $limit);
    }

    /**
     * @param IteratorAggregate $iterable1
     * @param IteratorAggregate $iterable2
     * @return bool
     */
    public static function equal(IteratorAggregate $iterable1, IteratorAggregate $iterable2)
    {
        return Iterators::equal(
            new IteratorIterator($iterable1->getIterator()),
            new IteratorIterator($iterable2->getIterator())
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
