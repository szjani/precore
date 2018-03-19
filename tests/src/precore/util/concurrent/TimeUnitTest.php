<?php

declare(strict_types=1);

namespace precore\util\concurrent;

use PHPUnit\Framework\TestCase;

class TimeUnitTest extends TestCase
{
    public function testToSeconds()
    {
        self::assertEquals(1, TimeUnit::$MILLISECONDS->toSeconds(1000));
        self::assertEquals(0.5, TimeUnit::$MILLISECONDS->toSeconds(500));
        self::assertEquals(3 * 3600, TimeUnit::$HOURS->toSeconds(3));
        self::assertEquals(24 * 3600, TimeUnit::$DAYS->toSeconds(1));
        $days = 365;
        $seconds = TimeUnit::$DAYS->toSeconds($days);
        $resultDays = TimeUnit::$SECONDS->toDays($seconds);
        self::assertEquals($days, $resultDays);
    }

    public function testToHours()
    {
        self::assertEquals(24, TimeUnit::$DAYS->toHours(1));
        self::assertEquals(2, TimeUnit::$HOURS->toHours(2));
    }

    public function testConvert()
    {
        self::assertEquals(10 * 1000 * 60, TimeUnit::$MILLISECONDS->convert(10, TimeUnit::$MINUTES));
        self::assertEquals(24, TimeUnit::$HOURS->convert(1, TimeUnit::$DAYS));
        self::assertEquals(2 / 60, TimeUnit::$HOURS->convert(2, TimeUnit::$MINUTES));
    }

    public function testToMillis()
    {
        self::assertEquals(1000, TimeUnit::$SECONDS->toMillis(1));
    }

    public function testToMicros()
    {
        self::assertEquals(1000, TimeUnit::$MILLISECONDS->toMicros(1));
    }

    public function testToMinutes()
    {
        self::assertEquals(1, TimeUnit::$SECONDS->toMinutes(60));
    }

    public function testSleep()
    {
        $duration = 0.1;
        $start = microtime(true);
        TimeUnit::$SECONDS->sleep($duration);
        $end = microtime(true);
        self::assertTrue(($end - $start) < ($duration * 1.2));
    }

    public function testToString()
    {
        self::assertEquals('SECONDS', TimeUnit::$SECONDS->toString());
    }

    public function testToDateInterval()
    {
        self::assertEquals('1 seconds', TimeUnit::$SECONDS->toDateInterval(1)->format('%s seconds'));
        self::assertEquals('4 minutes', TimeUnit::$MINUTES->toDateInterval(4)->format('%i minutes'));
        self::assertEquals('2 hours', TimeUnit::$HOURS->toDateInterval(2)->format('%h hours'));
        self::assertEquals('3 days', TimeUnit::$DAYS->toDateInterval(3)->format('%d days'));
    }

    /**
     * @expectedException \RuntimeException
     * @test
     */
    public function shouldThrowExceptionToDateIntervalOnMicroseconds()
    {
        TimeUnit::$MILLISECONDS->toDateInterval(1);
    }

    /**
     * @expectedException \RuntimeException
     * @test
     */
    public function shouldThrowExceptionToDateIntervalOnMilliseconds()
    {
        TimeUnit::$MILLISECONDS->toDateInterval(1);
    }
}
