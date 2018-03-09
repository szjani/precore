<?php
declare(strict_types=1);

namespace precore\util;

/**
 * A time source; returns a time value representing the number of microseconds elapsed
 * since some fixed but arbitrary point in time.
 * Note that most users should use Stopwatch instead of interacting with this class directly.
 *
 * Warning: this interface can only be used to measure elapsed time, not wall time.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class Ticker
{
    private static $systemTicker;

    protected function __construct()
    {
    }

    public static function init() : void
    {
        self::$systemTicker = new SystemTicker();
    }

    /**
     * Returns the number of microseconds elapsed since this ticker's fixed point of reference.
     *
     * @return float
     */
    public abstract function read() : float;

    /**
     * @return Ticker
     */
    public static function systemTicker() : Ticker
    {
        return self::$systemTicker;
    }
}
Ticker::init();

final class SystemTicker extends Ticker
{
    /**
     * Returns the number of microseconds elapsed since this ticker's fixed point of reference.
     *
     * @return float
     */
    public function read() : float
    {
        return microtime(true) * 1000000;
    }
}
