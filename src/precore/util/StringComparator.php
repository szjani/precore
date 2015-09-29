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

    private function __construct($function)
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
    public function compare($object1, $object2)
    {
        return Functions::call($this->function, $object1, $object2);
    }
}
StringComparator::init();
