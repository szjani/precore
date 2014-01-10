<?php
/*
 * Copyright (c) 2012 Szurovecz János
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

namespace precore\util\concurrent;

use precore\lang\Enum;

class TimeUnit extends Enum
{
    const C0 = 1;
    const C1 = 1000;
    const C2 = 1000000;
    const C3 = 60000000;
    const C4 = 3600000000;
    const C5 = 86400000000;

    /**
     * @var TimeUnit
     */
    public static $MICROSECONDS;

    /**
     * @var TimeUnit
     */
    public static $MILLISECONDS;

    /**
     * @var TimeUnit
     */
    public static $SECONDS;

    /**
     * @var TimeUnit
     */
    public static $MINUTES;

    /**
     * @var TimeUnit
     */
    public static $HOURS;

    /**
     * @var TimeUnit
     */
    public static $DAYS;

    private $inMicros;

    protected static function constructorArgs()
    {
        return array(
            'MICROSECONDS' => array(self::C0),
            'MILLISECONDS' => array(self::C1),
            'SECONDS' => array(self::C2),
            'MINUTES' => array(self::C3),
            'HOURS' => array(self::C4),
            'DAYS' => array(self::C5)
        );
    }

    protected function __construct($inMicros)
    {
        $this->inMicros = $inMicros;
    }

    public function toMicros($duration)
    {
        return $duration * ($this->inMicros / self::C0);
    }

    public function toMillis($duration)
    {
        return $duration * ($this->inMicros / self::C1);
    }

    public function toSeconds($duration)
    {
        return $duration * ($this->inMicros / self::C2);
    }

    public function toMinutes($duration)
    {
        return $duration * ($this->inMicros / self::C3);
    }

    public function toHours($duration)
    {
        return $duration * ($this->inMicros / self::C4);
    }

    public function toDays($duration)
    {
        return $duration * ($this->inMicros / self::C5);
    }

    public function convert($duration, TimeUnit $timeUnit)
    {
        return $duration * ($timeUnit->inMicros / $this->inMicros);
    }

    public function sleep($duration)
    {
        usleep($this->toMicros($duration));
    }
}
TimeUnit::init();
