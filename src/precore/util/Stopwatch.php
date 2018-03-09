<?php
declare(strict_types=1);

namespace precore\util;

use precore\lang\IllegalStateException;
use precore\lang\BaseObject;
use precore\util\concurrent\TimeUnit;
use RuntimeException;

/**
 * An object that measures elapsed time in microseconds. It is useful to measure
 * elapsed time using this class instead of direct calls to {@link microtime()} for a few reasons:
 *
 * <ul>
 *   <li>
 *     An alternate time source can be substituted, for testing or performance
 *     reasons.
 *   </li>
 *   <li>
 *     As documented by {@link microtime()}, the value returned has no absolute
 *     meaning, and can only be interpreted as relative to another timestamp
 *     returned by {@link microtime()} at a different time. {@link Stopwatch} is a
 *     more effective abstraction because it exposes only these relative values,
 *     not the absolute ones.
 *   </li>
 * </ul>
 *
 * <p>Basic usage:
 * <pre>
 *   $stopwatch = Stopwatch::createStarted();
 *   doSomething();
 *   $stopwatch->stop(); // optional
 *   $millis = $stopwatch->elapsed(TimeUnit::$MILLISECONDS);
 *   $log->info("time: " . $stopwatch); // formatted string like "12.3 ms"
 * </pre>
 *
 * <p>
 *   Stopwatch methods are not idempotent; it is an error to start or stop a
 *   stopwatch that is already in the desired state.
 * </p>
 *
 * <p>When testing code that uses this class, use
 * {@link Stopwatch::createUnstartedWith($ticker)} or {@link Stopwatch::createStartedWith($ticker)} to
 * supply a fake or mock ticker.
 *
 * @see https://code.google.com/p/guava-libraries/source/browse/guava/src/com/google/common/base/Stopwatch.java
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Stopwatch extends BaseObject
{
    /**
     * @var Ticker
     */
    private $ticker;

    /**
     * @var boolean
     */
    private $isRunning = false;

    /**
     * @var float
     */
    private $elapsedMicros = 0;

    /**
     * @var float
     */
    private $startTick = 0;

    /**
     * @param Ticker $ticker
     */
    private function __construct(Ticker $ticker)
    {
        $this->ticker = $ticker;
    }

    /**
     * @return Stopwatch
     */
    public static function createUnstarted() : Stopwatch
    {
        return new Stopwatch(Ticker::systemTicker());
    }

    /**
     * @return Stopwatch
     */
    public static function createStarted() : Stopwatch
    {
        return self::createUnstarted()->start();
    }

    /**
     * @param Ticker $ticker
     * @return Stopwatch
     */
    public static function createUnstartedWith(Ticker $ticker) : Stopwatch
    {
        return new Stopwatch($ticker);
    }

    /**
     * @param Ticker $ticker
     * @return Stopwatch
     */
    public static function createStartedWith(Ticker $ticker) : Stopwatch
    {
        return self::createUnstartedWith($ticker)->start();
    }

    /**
     * Starts the stopwatch.
     *
     * @return $this
     * @throws IllegalStateException if this Stopwatch is already running
     */
    public function start() : Stopwatch
    {
        Preconditions::checkState(!$this->isRunning, 'This stopwatch is already running.');
        $this->isRunning = true;
        $this->startTick = $this->ticker->read();
        return $this;
    }

    /**
     * Stops the stopwatch. Future reads will return the fixed duration that had
     * elapsed up to this point.
     *
     * @throw IllegalStateException if it is already stopped
     * @return $this
     * @throws IllegalStateException if this Stopwatch is already stopped
     */
    public function stop() : Stopwatch
    {
        $tick = $this->ticker->read();
        Preconditions::checkState($this->isRunning, 'This stopwatch is already stopped.');
        $this->isRunning = false;
        $this->elapsedMicros += ($tick - $this->startTick);
        return $this;
    }

    /**
     * Sets the elapsed time for this stopwatch to zero,
     * and places it in a stopped state.
     *
     * @return $this
     */
    public function reset() : Stopwatch
    {
        $this->elapsedMicros = 0;
        $this->isRunning = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRunning() : bool
    {
        return $this->isRunning;
    }

    /**
     * @param TimeUnit $desiredUnit
     * @return float
     */
    public function elapsed(TimeUnit $desiredUnit) : float
    {
        return $desiredUnit->convert($this->elapsedMicros(), TimeUnit::$MICROSECONDS);
    }

    /**
     * @return float
     */
    private function elapsedMicros() : float
    {
        return $this->isRunning
            ? $this->ticker->read() - $this->startTick
            : $this->elapsedMicros;
    }

    public function toString() : string
    {
        $micros = $this->elapsedMicros();

        $value = $micros;
        $resultUnit = TimeUnit::$MICROSECONDS;
        /* @var $currentUnit TimeUnit */
        foreach (array_reverse(TimeUnit::values()) as $currentUnit) {
            $currentValue = $currentUnit->convert($micros, TimeUnit::$MICROSECONDS);
            if (floor($currentValue) > 0) {
                $value = $currentValue;
                $resultUnit = $currentUnit;
                break;
            }
        }
        return sprintf("%.4g %s", $value, self::abbreviate($resultUnit));
    }

    private static function abbreviate(TimeUnit $unit) : string
    {
        switch ($unit) {
            case TimeUnit::$MICROSECONDS:
                return "µs";
            case TimeUnit::$MILLISECONDS:
                return "ms";
            case TimeUnit::$SECONDS:
                return "s";
            case TimeUnit::$MINUTES:
                return "min";
            case TimeUnit::$HOURS:
                return "h";
            case TimeUnit::$DAYS:
                return "d";
            default:
                throw new RuntimeException("Unhandled TimeUnit " . $unit);
        }
    }
}
