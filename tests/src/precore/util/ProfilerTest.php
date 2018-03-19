<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;

/**
 * Class ProfilerTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ProfilerTest extends TestCase
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
