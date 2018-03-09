<?php
declare(strict_types=1);

namespace precore\util;

use precore\lang\ClassCastException;
use precore\lang\Enum;

/**
 * Provides {@link Comparator} objects for string comparison based on native functions.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class StringComparator extends Enum implements Comparator
{
    /**
     * @see strcmp
     * @var StringComparator
     */
    public static $BINARY;

    /**
     * @see strcasecmp
     * @var StringComparator
     */
    public static $BINARY_CASE_INSENSITIVE;

    /**
     * @see strnatcmp
     * @var StringComparator
     */
    public static $NATURAL;

    /**
     * @see strnatcasecmp
     * @var StringComparator
     */
    public static $NATURAL_CASE_INSENSITIVE;

    private $function;

    protected static function constructorArgs()
    {
        return [
            'BINARY' => ['strcmp'],
            'BINARY_CASE_INSENSITIVE' => ['strcasecmp'],
            'NATURAL' => ['strnatcmp'],
            'NATURAL_CASE_INSENSITIVE' => ['strnatcasecmp']
        ];
    }

    private function __construct(callable $function)
    {
        $this->function = $function;
    }

    /**
     * @param $object1
     * @param $object2
     * @return int a negative integer, zero, or a positive integer
     *         as the first argument is less than, equal to, or greater than the second.
     * @throws ClassCastException - if the arguments' types prevent them from being compared by this comparator.
     */
    public function compare($object1, $object2) : int
    {
        return Functions::call($this->function, $object1, $object2);
    }
}
StringComparator::init();
