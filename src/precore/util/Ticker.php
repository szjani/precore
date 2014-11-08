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

    public static function init()
    {
        self::$systemTicker = new SystemTicker();
    }

    /**
     * Returns the number of microseconds elapsed since this ticker's fixed point of reference.
     *
     * @return float
     */
    public abstract function read();

    /**
     * @return Ticker
     */
    public static function systemTicker()
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
    public function read()
    {
        return microtime(true) * 1000000;
    }
}
