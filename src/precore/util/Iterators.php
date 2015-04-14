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
use IteratorIterator;
use LimitIterator;
use MultipleIterator;

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
     * Creates an iterator that returns only those elements given by $iterator that are allowed by $predicate.
     *
     * @param Iterator $iterator
     * @param callable $predicate
     * @return Iterator
     */
    public static function filter(Iterator $iterator, callable $predicate)
    {
        return new CallbackFilterIterator($iterator, $predicate);
    }

    /**
     * Converts each element provided by $iterator with the given $transformer function.
     *
     * @param Iterator $iterator
     * @param callable $transformer
     * @return Iterator
     */
    public static function transform(Iterator $iterator, callable $transformer)
    {
        return new TransformerIterator($iterator, $transformer);
    }

    /**
     * @param Iterator $iterator
     * @param int $limit
     * @return Iterator
     */
    public static function limit(Iterator $iterator, $limit)
    {
        return new LimitIterator($iterator, 0, $limit);
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
     * @param Iterator $iterator
     * @return boolean
     */
    public static function isEmpty(Iterator $iterator)
    {
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
    public static function equal(Iterator $iterator1, Iterator $iterator2)
    {
        $multipleIterator = new MultipleIterator(MultipleIterator::MIT_NEED_ANY | MultipleIterator::MIT_KEYS_NUMERIC);
        $multipleIterator->attachIterator($iterator1);
        $multipleIterator->attachIterator($iterator2);
        foreach ($multipleIterator as $items) {
            if (!Objects::equal($items[0], $items[1])) {
                return false;
            }
        }
        return true;
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
