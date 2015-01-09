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

use precore\lang\Object;

/**
 * <p> Helps collection execution times in a programmatic way.
 *
 * <p> All the time the start() method is called, a new stopwatch will be started, and the previous one will be stopped.
 * Calling the stop() method stops the whole profiler, does not start another stopwatch.
 *
 * <p> Obtaining the result can be accomplished via the log() or the printout() method.
 *
 * <p> Nested profiler can be started by the startNested() method. The currently active entry will include
 * the total time of the nested profiler. All nested profilers are registered to the {@link ProfilerRegistry}
 * and can be got from there.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Profiler extends Object
{
    const ENTRY_FORMAT = "%-13s%30s%10s.";
    const ENTRY_PREFIX = ' |-- ';
    const HEAD_PREFIX = ' ';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $currentName;

    /**
     * All items can be cast to string.
     *
     * @var object[]
     */
    private $entries = [];

    /**
     * @var Stopwatch
     */
    private $globalStopwatch;

    /**
     * @var Stopwatch
     */
    private $entryStopwatch;

    /**
     * @var int
     */
    private $level = 1;

    /**
     * @var Profiler|null
     */
    private $parent = null;

    /**
     * @var null|Ticker
     */
    private $ticker;

    /**
     * @param string $name The name of this profile
     * @param Ticker $ticker should not be used in production code
     */
    public function __construct($name, Ticker $ticker = null)
    {
        $this->name = $name;
        if ($ticker === null) {
            $this->globalStopwatch = Stopwatch::createUnstarted();
            $this->entryStopwatch = Stopwatch::createUnstarted();
        } else {
            $this->globalStopwatch = Stopwatch::createUnstartedWith($ticker);
            $this->entryStopwatch = Stopwatch::createUnstartedWith($ticker);
        }
        $this->ticker = $ticker;
    }

    /**
     * Creates and starts a new entry.
     *
     * @param string $name The name of the next entry
     * @return $this
     */
    public function start($name)
    {
        if (!$this->globalStopwatch->isRunning()) {
            $this->globalStopwatch->start();
        }
        if ($this->entryStopwatch->isRunning()) {
            $this->recordEntry();
        }
        $this->currentName = $name;
        $this->entryStopwatch->start();
        return $this;
    }

    /**
     * Stops the whole profiler.
     *
     * @return $this
     */
    public function stop()
    {
        $this->recordEntry();
        $this->globalStopwatch->stop();
        return $this;
    }

    /**
     * Creates a nested profiler, which can be obtained from {@link ProfilerRegistry} by its name.
     *
     * @param string $name
     * @return $this
     */
    public function startNested($name)
    {
        $nestedProfiler = new Profiler($name, $this->ticker);
        $nestedProfiler->parent = $this;
        $nestedProfiler->level = $this->level + 1;
        $this->entries[] = $nestedProfiler;
        ProfilerRegistry::instance()->register($name, $nestedProfiler);
        return $this;
    }

    public function toString()
    {
        $res = sprintf('%' . (($this->level - 1) * 4) . "s+ Profiler [%s]" . PHP_EOL, self::HEAD_PREFIX, $this->name);
        foreach ($this->entries as $entry) {
            $res .= $entry;
        }
        $total = $this->parent === null ? 'Total' : 'Subtotal';
        return $res . $this->entryString($total, '[' . $this->name . ']', $this->globalStopwatch);
    }

    /**
     * Sends the {@link Profiler::toString()} to the standard output.
     */
    public function printOut()
    {
        echo $this->toString();
    }

    /**
     * Creates a log entry on DEBUG level.
     */
    public function log()
    {
        self::getLogger()->debug("Profiler output:{}{}", [PHP_EOL, $this]);
    }

    private function recordEntry()
    {
        $this->entryStopwatch->stop();
        $this->entries[] = $this->entryString('elapsed time', '[' . $this->currentName . ']', $this->entryStopwatch);
        $this->entryStopwatch->reset();
    }

    private function entryString($title, $stopwatchName, Stopwatch $stopwatch)
    {
        $format = '%' . ($this->level * 4) . 's' . self::ENTRY_FORMAT . PHP_EOL;
        return sprintf($format, self::ENTRY_PREFIX, $title, $stopwatchName, $stopwatch);
    }
}
