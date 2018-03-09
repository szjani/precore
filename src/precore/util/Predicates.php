<?php
declare(strict_types=1);

namespace precore\util;

use ArrayObject;
use precore\lang\ObjectClass;
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
    public static function alwaysTrue() : callable
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
    public static function alwaysFalse() : callable
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
    public static function not(callable $predicate) : callable
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
    public static function isNull() : callable
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
    public static function notNull() : callable
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
    public static function call(callable $predicate, $element) : bool
    {
        return (boolean) Functions::call($predicate, $element);
    }

    /**
     * Returns true if and only if all given predicates return true.
     *
     * @param callable[] ...$predicates
     * @return callable
     */
    public static function ands(callable ...$predicates) : callable
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
    public static function ors(callable $predicates) : callable
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
    public static function equalTo($target) : callable
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
    public static function instance(string $class) : callable
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
    public static function in(Traversable $traversable) : callable
    {
        return function ($element) use ($traversable) {
            return Iterators::contains(Iterators::from($traversable), $element);
        };
    }

    /**
     * Returns true if the predicate parameter is contained by the given array.
     *
     * @param array $array
     * @return callable
     */
    public static function inArray(array $array) : callable
    {
        return self::in(new ArrayObject($array));
    }

    /**
     * Returns true if the predicate parameter matches to the given regular expression.
     *
     * @param $pattern
     * @return callable
     */
    public static function matches(string $pattern) : callable
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
    public static function compose(callable $predicate, callable $function) : callable
    {
        return function ($element) use ($predicate, $function) {
            return Predicates::call($predicate, Functions::call($function, $element));
        };
    }

    /**
     * @param string $class
     * @return callable
     */
    public static function assignableFrom(string $class) : callable
    {
        return function ($inputClass) use ($class) {
            return ObjectClass::forName($inputClass)->isAssignableFrom(ObjectClass::forName($class));
        };
    }
}
