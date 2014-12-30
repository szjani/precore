<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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

use PHPUnit_Framework_TestCase;
use precore\util\concurrent\TimeUnit;

/**
 * Class StopwatchTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class StopwatchTest extends PHPUnit_Framework_TestCase
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

