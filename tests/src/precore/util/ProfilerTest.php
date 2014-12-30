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

/**
 * Class ProfilerTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ProfilerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     */
    public function shouldThrowExceptionStopWithoutStart()
    {
        $profiler = new Profiler(__METHOD__);
        $profiler->stop();
    }

    /**
     * @test
     */
    public function shouldStartStopDoFourTicks()
    {
        $ticker = IncrementedTicker::instance();
        $profiler = new Profiler(__METHOD__, $ticker);
        $profiler->start('t1');
        $profiler->stop();
        self::assertEquals(4, $ticker->counter());
    }

    /**
     * @test
     */
    public function shouldToStringContainAllInfo()
    {
        $ticker = IncrementedTicker::instance();
        $profiler = new Profiler('p1', $ticker);
        $profiler->start('t1');
        $profiler->start('t2');
        $profiler->stop();
        $expected = <<<'EOT'
 + Profiler [p1]
 |-- elapsed time                           [t1]     1 µs.
 |-- elapsed time                           [t2]     1 µs.
 |-- Total                                  [p1]     5 µs.

EOT;
        self::assertEquals($expected, $profiler->toString());
    }

    /**
     * @test
     */
    public function shouldToStringContainNestedProfiler()
    {
        $ticker = IncrementedTicker::instance();
        $profiler = new Profiler('p1', $ticker);
        $profiler->start('t1');
        $profiler->startNested('np1');
        $nested = ProfilerRegistry::instance()->get('np1');
        $nested->start('np1-t1');
        $nested->stop();
        $profiler->start('t2');
        $profiler->stop();
        $expected = <<<'EOT'
 + Profiler [p1]
    + Profiler [np1]
    |-- elapsed time                       [np1-t1]     1 µs.
    |-- Subtotal                              [np1]     3 µs.
 |-- elapsed time                           [t1]     5 µs.
 |-- elapsed time                           [t2]     1 µs.
 |-- Total                                  [p1]     9 µs.

EOT;
        self::assertEquals($expected, $profiler->toString());
    }
};
