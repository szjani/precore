<?php

namespace precore\util;

use CallbackFilterIterator;
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
