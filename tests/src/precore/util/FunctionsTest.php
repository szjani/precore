<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;
use precore\util\concurrent\TimeUnit;

/**
 * Class FunctionsTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class FunctionsTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnTheSameValueAllTheTime()
    {
        $value = 1;
        $function = Functions::constant($value);
        self::assertEquals($value, call_user_func($function, null));
        self::assertEquals($value, call_user_func($function, 1));
        self::assertEquals($value, call_user_func($function, 2));
        self::assertEquals($value, call_user_func($function, 'a'));
    }

    /**
     * @test
     */
    public function shouldReturnTheInput()
    {
        self::assertSame(1, call_user_func(Functions::identity(), 1));
        self::assertSame(2, call_user_func(Functions::identity(), 2));
        self::assertSame(null, call_user_func(Functions::identity(), null));
        self::assertSame('a', call_user_func(Functions::identity(), 'a'));
    }

    /**
     * @test
     */
    public function shouldReturnToString()
    {
        self::assertSame('1', call_user_func(Functions::toStringFunction(), 1));
        self::assertSame('a', call_user_func(Functions::toStringFunction(), 'a'));
        self::assertSame(TimeUnit::$DAYS->toString(), call_user_func(Functions::toStringFunction(), TimeUnit::$DAYS));
    }

    /**
     * @test
     */
    public function shouldReturnComposite()
    {
        $duplicate = function ($number) {
            return $number * 2;
        };
        $increment = function ($number) {
            return $number + 1;
        };
        self::assertEquals(3, call_user_func(Functions::compose($increment, $duplicate), 1));
        self::assertEquals(4, call_user_func(Functions::compose($duplicate, $increment), 1));
    }

    /**
     * @test
     */
    public function shouldReturnFromMap()
    {
        $map = ['a', 'b', 'c'];
        self::assertEquals('a', call_user_func(Functions::forMap($map), 0));
        self::assertEquals('b', call_user_func(Functions::forMap($map), 1));
        self::assertEquals('c', call_user_func(Functions::forMap($map), 2));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfNotInMap()
    {
        $map = ['a'];
        call_user_func(Functions::forMap($map), 1);
    }

    /**
     * @test
     */
    public function shouldReturnFromMapOrDefault()
    {
        $map = ['a'];
        self::assertEquals('a', call_user_func(Functions::forMapOr($map, null), 0));
        self::assertEquals(null, call_user_func(Functions::forMapOr($map, null), 1));
    }
}
