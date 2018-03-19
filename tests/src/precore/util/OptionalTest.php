<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;

/**
 * Class OptionalTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class OptionalTest extends TestCase
{
    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     */
    public function shouldThrowISEIfAbsent()
    {
        Optional::absent()->get();
    }

    /**
     * @test
     */
    public function shouldReturnInstance()
    {
        $instance = 'instance';
        self::assertEquals($instance, Optional::of($instance)->get());
    }

    /**
     * @test
     */
    public function shouldCreateOptionalFromNullable()
    {
        self::assertSame(Optional::absent(), Optional::ofNullable(null));
        $instance = 'instance';
        self::assertEquals($instance, Optional::ofNullable($instance)->get());
    }

    /**
     * @test
     */
    public function shouldReturnIsPresent()
    {
        self::assertFalse(Optional::absent()->isPresent());
        self::assertTrue(Optional::of(1)->isPresent());
    }

    /**
     * @test
     */
    public function shouldReturnOrNull()
    {
        self::assertNull(Optional::absent()->orNull());
        $instance = 'instance';
        self::assertEquals($instance, Optional::of($instance)->orNull());
    }

    /**
     * @test
     */
    public function shouldReturnDefaultValue()
    {
        $defaultValue = 'default';
        self::assertSame($defaultValue, Optional::absent()->orElse($defaultValue));
    }

    /**
     * @test
     */
    public function shouldOmitDefaultValue()
    {
        $instance = 'instance';
        self::assertSame($instance, Optional::of($instance)->orElse('default'));
    }

    /**
     * @test
     */
    public function shouldCheckEquality()
    {
        self::assertTrue(Optional::absent()->equals(Optional::absent()));
        self::assertTrue(Optional::of('instance')->equals(Optional::of('instance')));
    }

    /**
     * @test
     */
    public function shouldMapOptional()
    {
        $mapper = function ($number) {
            return $number * 2;
        };
        self::assertSame(Optional::absent(), Optional::absent()->map($mapper));
        self::assertEquals(6, Optional::of(3)->map($mapper)->get());
        self::assertSame(Optional::absent(), Optional::of('any')->map(Functions::constant(null)));
    }

    /**
     * @test
     */
    public function shouldFilter()
    {
        self::assertSame(Optional::absent(), Optional::absent()->filter(Predicates::isNull()));
        self::assertEquals(Optional::of(2), Optional::of(2)->filter(Predicates::notNull()));
        self::assertEquals(Optional::absent(), Optional::of(1)->filter(Predicates::isNull()));
    }

    /**
     * @test
     */
    public function shouldReturnOrElseGet()
    {
        $supplier = function () {
            return 3;
        };
        self::assertSame(3, Optional::absent()->orElseGet($supplier));
        self::assertSame(1, Optional::of(1)->orElseGet($supplier));
    }

    /**
     * @test
     * @expectedException \precore\lang\NullPointerException
     */
    public function shouldThrowNPEIfValueIsNotPresentAndOtherIsNull()
    {
        Optional::absent()->orElseGet(null);
    }

    /**
     * @test
     */
    public function shouldCallIfPresent()
    {
        $called = null;
        $consumer = function ($param) use (&$called) {
            $called = $param;
        };
        Optional::of(2)->ifPresent($consumer);
        self::assertEquals(2, $called);
    }

    /**
     * @test
     * @expectedException \precore\lang\NullPointerException
     */
    public function shouldThrowNPEIfConsumerIsNull()
    {
        Optional::of(2)->ifPresent(null);
    }

    /**
     * @test
     */
    public function shouldNotCallConsumerIfAbsent()
    {
        $called = 0;
        $consumer = function ($param) use (&$called) {
            $called++;
        };
        Optional::absent()->ifPresent($consumer);
        self::assertEquals(0, $called);
        Optional::absent()->ifPresent(null);
    }

    /**
     * @test
     */
    public function shouldFlatMapAbsent()
    {
        self::assertTrue(Optional::absent()->flatMap(function () {})->equals(Optional::absent()));
    }

    /**
     * @test
     */
    public function shouldFlatMapNonAbsent()
    {
        $mapper = function ($value) {
            return Optional::of(2 * $value);
        };
        self::assertTrue(Optional::of(1)->flatMap($mapper)->equals(Optional::of(2)));
    }

    /**
     * @test
     */
    public function shouldReturnOrElseThrow()
    {
        self::assertEquals(2, Optional::of(2)->orElseThrow(null));
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage ouch
     */
    public function shouldThrowReturnOrElseThrow()
    {
        $exceptionSupplier = function () {
            return new \Exception("ouch");
        };
        Optional::absent()->orElseThrow($exceptionSupplier);
    }
}
