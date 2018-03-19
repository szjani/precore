<?php
declare(strict_types=1);

namespace precore\util;

use precore\lang\Comparable;

/**
 * @package precore\util
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class Arrays
{
    private function __construct()
    {
    }

    /**
     * Sorts the specified array according to the order induced by the specified comparator.
     * All elements in the array must be mutually comparable using the specified comparator
     * (that is, c.compare(e1, e2) must not throw a ClassCastException for any elements e1 and e2 in the array).
     *
     * <p>
     * If the specified comparator is null, this method sorts the specified array into ascending order,
     * according to the natural ordering of its elements. All elements in the array must implement
     * the {@link Comparable} interface. Furthermore, all elements in the array must be mutually comparable
     * (that is, e1.compareTo(e2) must not throw a ClassCastException for any elements e1 and e2 in the array).
     *
     * @param Comparable[] $list
     * @param Comparator $comparator
     */
    public static function sort(array &$list, Comparator $comparator = null) : void
    {
        if ($comparator === null) {
            $comparator = ComparableComparator::instance();
        }
        usort($list, Collections::compareFunctionFor($comparator));
    }
}
