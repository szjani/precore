<?php
declare(strict_types=1);

namespace precore\util;

/**
 * Utility class for booleans.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Booleans
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
     * @param boolean $a
     * @param boolean $b
     * @return int
     */
    public static function compare(bool $a, bool $b) : int
    {
        return $a === $b ? 0 : ($a ? 1 : -1);
    }

    /**
     * @return Ordering
     */
    public static function naturalOrdering() : Ordering
    {
        return self::$NATURAL;
    }
}
Booleans::init();
