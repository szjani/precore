<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;
use precore\util\concurrent\TimeUnit;

/**
 * Class StopwatchTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class StopwatchTest extends TestCase
{
    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     */
    public function shouldThrowExceptionIfAlreadyStopped()
    {
        $stopwatch = Stopwatch::createStarted();
        $stopwatch->stop();
        $stopwatch->stop();
    }

    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     */
    public function shouldThrowExceptionIfAlreadyStarted()
    {
        $stopwatch = Stopwatch::createStarted();
        $stopwatch->start();
    }

    /**
     * @test
     */
    public function shouldBeStopped()
    {
        $stopwatch = Stopwatch::createStarted();
        $stopwatch->stop();
        self::assertFalse($stopwatch->isRunning());
    }

    /**
     * @test
     */
    public function shouldBeTheSameElapsedAfterStop()
    {
        $stopwatch = Stopwatch::createStartedWith(IncrementedTicker::instance());
        $stopwatch->stop();
        $time1 = $stopwatch->elapsed(TimeUnit::$MICROSECONDS);
        $time2 = $stopwatch->elapsed(TimeUnit::$MICROSECONDS);
        self::assertEquals($time1, $time2);
    }

    /**
     * @test
     */
    public function shouldBeDifferentElapsedIfNotStopped()
    {
        $stopwatch = Stopwatch::createStartedWith(IncrementedTicker::instance());
        $time1 = $stopwatch->elapsed(TimeUnit::$MICROSECONDS);
        $time2 = $stopwatch->elapsed(TimeUnit::$MICROSECONDS);
        self::assertNotEquals($time1, $time2);
    }

    /**
     * @test
     */
    public function shouldReset()
    {
        $stopwatch = Stopwatch::createStarted();
        TimeUnit::$MILLISECONDS->sleep(50);
        self::assertTrue($stopwatch->isRunning());
        $stopwatch->stop();
        $stopwatch->reset();
        self::assertEquals(0, $stopwatch->elapsed(TimeUnit::$MICROSECONDS));
        self::assertFalse($stopwatch->isRunning());
    }

    /**
     * @test
     */
    public function shouldContinueAfterStopStart()
    {
        $stopwatch = Stopwatch::createStartedWith(IncrementedTicker::instance());
        $stopwatch->stop();
        $time1 = $stopwatch->elapsed(TimeUnit::$MICROSECONDS);
        $stopwatch->start();
        $stopwatch->stop();
        $time2 = $stopwatch->elapsed(TimeUnit::$MICROSECONDS);
        self::assertNotEquals($time2, $time1);
    }

    /**
     * @test
     */
    public function shouldProvideToString()
    {
        $stopwatch = Stopwatch::createStartedWith(IncrementedTicker::instance());
        $stopwatch->stop();
        self::assertEquals("1 µs", $stopwatch->toString());
    }
}

