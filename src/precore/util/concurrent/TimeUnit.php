<?php

declare(strict_types=1);

namespace precore\util\concurrent;

use DateInterval;
use precore\lang\Enum;
use precore\lang\IllegalStateException;
use precore\util\Preconditions;

class TimeUnit extends Enum
{
    const C0 = 1;
    const C1 = 1000;
    const C2 = 1000000;
    const C3 = 60000000;
    const C4 = 3600000000;
    const C5 = 86400000000;

    /**
     * Does not support toDateInterval()
     *
     * @var TimeUnit
     */
    public static $MICROSECONDS;

    /**
     * Does not support toDateInterval()
     *
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
    private $dateIntervalFormat;

    protected static function constructorArgs()
    {
        return [
            'MICROSECONDS' => [self::C0, null],
            'MILLISECONDS' => [self::C1, null],
            'SECONDS' => [self::C2, 'PT%dS'],
            'MINUTES' => [self::C3, 'PT%dM'],
            'HOURS' => [self::C4, 'PT%dH'],
            'DAYS' => [self::C5, 'P%dD']
        ];
    }

    /**
     * @param $inMicros float The amount of microseconds of this unit.
     * @param $dateIntervalFormat string the format character for DateInterval
     */
    protected function __construct($inMicros, $dateIntervalFormat)
    {
        $this->inMicros = $inMicros;
        $this->dateIntervalFormat = $dateIntervalFormat;
    }

    /**
     * Equivalent to TimeUnit::$MICROSECONDS->convert($duration, $this).
     *
     * @param $duration float
     * @return float
     */
    public function toMicros(float $duration) : float
    {
        return TimeUnit::$MICROSECONDS->convert($duration, $this);
    }

    /**
     * Equivalent to TimeUnit::$MILLISECONDS->convert($duration, $this).
     *
     * @param $duration float
     * @return float
     */
    public function toMillis(float $duration) : float
    {
        return TimeUnit::$MILLISECONDS->convert($duration, $this);
    }

    /**
     * Equivalent to TimeUnit::$SECONDS->convert(duration, $this).
     *
     * @param $duration float
     * @return float
     */
    public function toSeconds(float $duration) : float
    {
        return TimeUnit::$SECONDS->convert($duration, $this);
    }

    /**
     * Equivalent to TimeUnit::$MINUTES->convert(duration, $this).
     *
     * @param $duration float
     * @return float
     */
    public function toMinutes(float $duration) : float
    {
        return TimeUnit::$MINUTES->convert($duration, $this);
    }

    /**
     * Equivalent to TimeUnit::$HOURS->convert(duration, $this).
     *
     * @param $duration float
     * @return float
     */
    public function toHours(float $duration) : float
    {
        return TimeUnit::$HOURS->convert($duration, $this);
    }

    /**
     * Equivalent to TimeUnit::$DAYS->convert($duration, $this).
     *
     * @param $duration float
     * @return float
     */
    public function toDays(float $duration) : float
    {
        return TimeUnit::$DAYS->convert($duration, $this);
    }

    /**
     * Creates a DateInterval with the given duration and this unit.
     *
     * @param $duration float
     * @throws \Exception if this object does not have proper DateInterval format string
     * @return DateInterval
     */
    public function toDateInterval(float $duration) : DateInterval
    {
        Preconditions::checkState($this->dateIntervalFormat !== null, '[%s] does not support toDateInterval()', $this);
        return new DateInterval(sprintf($this->dateIntervalFormat, $duration));
    }

    /**
     * Convert the given time duration in the given unit to this unit.
     *
     * For example, to convert 10 minutes to milliseconds, use: TimeUnit::$MILLISECONDS->convert(10, TimeUnit::$MINUTES)
     *
     * @param $duration float The time duration in the given sourceUnit
     * @param TimeUnit $timeUnit the unit of the sourceDuration argument
     * @return float the converted duration in this unit
     */
    public function convert(float $duration, TimeUnit $timeUnit) : float
    {
        return $duration * ($timeUnit->inMicros / $this->inMicros);
    }

    /**
     * Sleeps the process using this TimeUnit.
     *
     * @param $duration
     */
    public function sleep(float $duration) : void
    {
        usleep(intval($this->toMicros($duration)));
    }
}
TimeUnit::init();
