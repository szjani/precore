<?php
declare(strict_types=1);

namespace precore\lang;

interface Comparable
{
    /**
     * @param $object
     * @return int a negative integer, zero, or a positive integer
     *         as this object is less than, equal to, or greater than the specified object.
     * @throws ClassCastException - if the specified object's type prevents it from being compared to this object.
     * @throws NullPointerException if the specified object is null
     */
    public function compareTo($object) : int;
}
