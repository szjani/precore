<?php

namespace precore\util;

use ArrayIterator;
use ArrayObject;
use PHPUnit_Framework_TestCase;

/**
 * Class IteratorsTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class IteratorsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldFilterOutNullElements()
    {
        $iterator = new ArrayIterator([1, 2, null, 3, null]);
        $result = Iterators::filter($iterator, Predicates::notNull());
        self::assertTrue(Iterators::equal($result, new ArrayIterator([1, 2, 3])));
    }

    /**
     * @test
     */
    public function shouldFilterOutEverything()
    {
        $iterator = new ArrayIterator([1, 2, null, 3, null]);
        $result = Iterators::filter($iterator, Predicates::alwaysFalse());
        self::assertTrue(Iterators::equal($result, new ArrayIterator()));
    }

    /**
     * @test
     */
    public function shouldIteratorsBeEqual()
    {
        $iterator1 = new ArrayIterator([1, 2, null, 3, null]);
        $iterator2 = new ArrayIterator([1, 2, null, 3, null]);
        self::assertTrue(Iterators::equal($iterator1, $iterator2));
        self::assertFalse(Iterators::equal($iterator1, new ArrayIterator()));
    }
}
