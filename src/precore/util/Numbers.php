<?php
declare(strict_types=1);

namespace precore\util;

/**
 * Utility class for numbers.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Numbers
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
     * @param $a
     * @param $b
     * @return int
     */
    public static function compare($a, $b) : int
    {
        $result = $a - $b;
        return $result < 0 ? -1 : ($result === 0 ? 0 : 1);
    }

    /**
     * @return Ordering
     */
    public static function naturalOrdering() : Ordering
    {
        return self::$NATURAL;
    }
}
Numbers::init();
