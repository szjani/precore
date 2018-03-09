<?php
declare(strict_types=1);

namespace precore\util;

use ArrayObject;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderingTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class OrderingTest extends TestCase
{
    /**
     * @test
     */
    public function shouldNullsFirst()
    {
        $array = ['a', 'c', null, 0, null, 'b'];
        Arrays::sort($array, Ordering::usingToString()->nullsFirst());
        self::assertEquals([null, null, '0', 'a', 'b', 'c'], $array);
    }

    /**
     * @test
     */
    public function shouldNullsLast()
    {
        $array = ['a', 'c', null, 0, null, 'b'];
        Arrays::sort($array, Ordering::usingToString()->nullsLast());
        self::assertEquals(['0', 'a', 'b', 'c', null, null], $array);
    }

    /**
     * @test
     */
    public function shouldReturnMin()
    {
        self::assertEquals('a', Ordering::usingToString()->min(new ArrayObject(['b', 'c', 'a', 'd'])));
    }

    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function shouldThrowExceptionMinOfEmptyInput()
    {
        Ordering::usingToString()->min(new ArrayObject([]));
    }

    /**
     * @test
     */
    public function shouldReturnMax()
    {
        self::assertEquals('d', Ordering::usingToString()->max(new ArrayObject(['b', 'c', 'd', 'a'])));
    }

    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function shouldThrowExceptionMaxOfEmptyInput()
    {
        Ordering::usingToString()->max(new ArrayObject([]));
    }

    /**
     * @test
     */
    public function shouldWorkSecondaryComparator()
    {
        $ordering = Ordering::from(StringComparator::$BINARY_CASE_INSENSITIVE)->compound(StringComparator::$BINARY);
        $array = ['a', 'A', 'a'];
        Arrays::sort($array, $ordering);
        self::assertEquals(['A', 'a', 'a'], $array);
    }
}
