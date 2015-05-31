<?php
/*
 * Copyright (c) 2012-2015 Janos Szurovecz
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
    public static function sort(array &$list, Comparator $comparator = null)
    {
        if ($comparator === null) {
            $comparator = ComparableComparator::instance();
        }
        usort($list, Collections::compareFunctionFor($comparator));
    }
}
