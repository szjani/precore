<?php

namespace precore\util;

use ArrayIterator;
use PHPUnit_Framework_TestCase;

/**
 * Class TransformerIteratorTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class TransformerIteratorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function shouldReturnTransformedData()
    {
        $iterator = new ArrayIterator([1, 2]);
        $transformedIterator = new TransformerIterator(
            $iterator,
            function ($number) {
                return 2 * $number;
            }
        );
        $transformedIterator->rewind();
        self::assertEquals(2, $transformedIterator->current());
        $transformedIterator->next();
        self::assertEquals(4, $transformedIterator->current());
    }
}
