<?php
declare(strict_types=1);

namespace precore\util;

use precore\lang\ClassCastException;
use precore\lang\Comparable;

/**
 * This comparator operates on {@link Comparable} objects.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ComparableComparator implements Comparator
{
    /**
     * @var ComparableComparator
     */
    private static $instance;

    public static function init() : void
    {
        self::$instance = new self();
    }

    /**
     * @return ComparableComparator
     */
    public static function instance() : ComparableComparator
    {
        return self::$instance;
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
        $this->checkType($object1);
        $this->checkType($object2);
        /* @var $object1 Comparable */
        return $object1->compareTo($object2);
    }

    private function checkType($object) : void
    {
        if (!($object instanceof Comparable)) {
            throw new ClassCastException(sprintf("Object must be an instance of 'precore\\lang\\Comparable'"));
        }
    }
}
ComparableComparator::init();
