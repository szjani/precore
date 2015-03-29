<?php

namespace precore\util;

use ArrayObject;
use PHPUnit_Framework_TestCase;

/**
 * Class IterablesTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class IterablesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldFilterOutNullElementsFromIterable()
    {
        $object = new ArrayObject([1, 2, null, 3, null]);
        $result = Iterables::filter($object, Predicates::notNull());
        self::assertTrue(Iterables::equal(new ArrayObject([1, 2, 3]), $result));
    }
}
