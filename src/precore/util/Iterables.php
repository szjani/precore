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
     * @param IteratorAggregate $iterable
     * @param $index
     * @return mixed
     */
    public static function get(IteratorAggregate $iterable, $index)
    {
        $traversable = $iterable->getIterator();
        if ($traversable instanceof Iterator) {
            return Iterators::get($traversable, $index);
        } elseif ($traversable instanceof IteratorAggregate) {
            return self::get($traversable, $index);
        }
        throw new InvalidArgumentException('Not supported built-in class');
    }

    /**
     * @param IteratorAggregate $iterable
     * @param $numberToSkip
     * @return IteratorAggregate
     */
    public static function skip(IteratorAggregate $iterable, $numberToSkip)
    {
        return new SkipIterable($iterable, $numberToSkip);
    }

    /**
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
     * @param IteratorAggregate $iterable
     * @param $element
     * @return boolean
     */
    public static function contains(IteratorAggregate $iterable, $element)
    {
        $traversable = $iterable->getIterator();
        if ($traversable instanceof Iterator) {
            return Iterators::contains($traversable, $element);
        } elseif ($traversable instanceof IteratorAggregate) {
            return self::contains($traversable, $element);
        }
        throw new InvalidArgumentException('Not supported built-in class');
    }

    /**
     * @param IteratorAggregate $iterable
     * @return boolean
     */
    public static function isEmpty(IteratorAggregate $iterable)
    {
        $traversable = $iterable->getIterator();
        if ($traversable instanceof Iterator) {
            return Iterators::isEmpty($traversable);
        } elseif ($traversable instanceof IteratorAggregate) {
            return self::isEmpty($traversable);
        }
        throw new InvalidArgumentException('Not supported built-in class');
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
