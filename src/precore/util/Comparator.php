<?php
declare(strict_types=1);

namespace precore\util;

use precore\lang\ClassCastException;

interface Comparator
{
    /**
     * @param $object1
     * @param $object2
     * @return int a negative integer, zero, or a positive integer
     *         as the first argument is less than, equal to, or greater than the second.
     * @throws ClassCastException - if the arguments' types prevent them from being compared by this comparator.
     */
    public function compare($object1, $object2) : int;
}
