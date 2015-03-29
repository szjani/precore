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

use ArrayObject;
use Traversable;

/**
 * Predicate is a function, which has one parameter, and returns a boolean value.
 * This class provides the most common predicates through static factory methods.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Predicates
{
    private function __construct()
    {
    }

    /**
     * This predicate always returns true.
     *
     * @return callable
     */
    public static function alwaysTrue()
    {
        return function () {
            return true;
        };
    }

    /**
     * This predicate always returns false.
     *
     * @return callable
     */
    public static function alwaysFalse()
    {
        return function () {
            return false;
        };
    }

    /**
     * This predicate returns the opposite of the given predicate.
     *
     * @param callable $predicate
     * @return callable
     */
    public static function not(callable $predicate)
    {
        return function ($element) use ($predicate) {
            return !self::call($predicate, $element);
        };
    }

    /**
     * This predicate returns true if and only if the parameter is null.
     *
     * @return callable
     */
    public static function isNull()
    {
        return function ($element) {
            return $element === null;
        };
    }

    /**
     * This predicate returns true if and only if the parameter is not null.
     *
     * @return callable
     */
    public static function notNull()
    {
        return function ($element) {
            return $element !== null;
        };
    }

    /**
     * Helper method to call a predicate with the given element as parameter.
     *
     * @param callable $predicate
     * @param $element
     * @return boolean
     */
    public static function call(callable $predicate, $element)
    {
        return (boolean) call_user_func($predicate, $element);
    }

    /**
     * Returns true if and only if all given predicates return true.
     *
     * @param callable... $predicates
     * @return callable
     */
    public static function ands(callable $predicates)
    {
        $predicates = func_get_args();
        return function ($element) use ($predicates) {
            foreach ($predicates as $predicate) {
                if (!self::call($predicate, $element)) {
                    return false;
                }
            }
            return true;
        };
    }

    /**
     * Returns true if at least one of the given predicates return true.
     *
     * @param callable $predicates
     * @return callable
     */
    public static function ors(callable $predicates)
    {
        $predicates = func_get_args();
        return function ($element) use ($predicates) {
            foreach ($predicates as $predicate) {
                if (self::call($predicate, $element)) {
                    return true;
                }
            }
            return false;
        };
    }

    /**
     * Returns true if the predicate parameter is equal to the given target.
     * It uses {@link Objects::equal()} to test equality.
     *
     * @param $target
     * @return callable
     */
    public static function equalTo($target)
    {
        return function ($element) use ($target) {
            return Objects::equal($element, $target);
        };
    }

    /**
     * Returns true if the predicate parameter is an instance of the given class.
     *
     * @param $class
     * @return callable
     */
    public static function instance($class)
    {
        return function ($element) use ($class) {
            return is_a($element, $class);
        };
    }

    /**
     * Returns true if the predicate parameter is contained by the given {@link Traversable}.
     *
     * @param Traversable $traversable
     * @return callable
     */
    public static function in(Traversable $traversable)
    {
        return function ($element) use ($traversable) {
            foreach ($traversable as $item) {
                if (Objects::equal($element, $item)) {
                    return true;
                }
            }
            return false;
        };
    }

    /**
     * Returns true if the predicate parameter is contained by the given array.
     *
     * @param array $array
     * @return callable
     */
    public static function inArray(array $array)
    {
        return self::in(new ArrayObject($array));
    }

    /**
     * Returns true if the predicate parameter matches to the given regular expression.
     *
     * @param $pattern
     * @return callable
     */
    public static function matches($pattern)
    {
        return function ($element) use ($pattern) {
            return preg_match($pattern, $element) === 1;
        };
    }

    /**
     * Returns true for $x if $predicate($function($x)) is true.
     *
     * @param callable $predicate
     * @param callable $function
     * @return callable
     */
    public static function compose(callable $predicate, callable $function)
    {
        return function ($element) use ($predicate, $function) {
            return Predicates::call($predicate, call_user_func($function, $element));
        };
    }
}
