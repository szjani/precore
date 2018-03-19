<?php
declare(strict_types=1);

namespace precore\util;

use DateTime;

/**
 * Utility class for {@link DateTime}.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class DateTimes
{
    private static $NATURAL;

    private function __construct()
    {
    }

    public static function init() : void
    {
        self::$NATURAL = Ordering::from(Collections::comparatorFrom([__CLASS__, 'compare']));
    }

    /**
     * @param DateTime $a
     * @param DateTime $b
     * @return int
     */
    public static function compare(DateTime $a, DateTime $b) : int
    {
        return $a == $b ? 0 : ($a < $b ? -1 : 1);
    }

    /**
     * @return Ordering
     */
    public static function naturalOrdering() : Ordering
    {
        return self::$NATURAL;
    }
}
DateTimes::init();
