<?php
declare(strict_types=1);

namespace precore\util;

/**
 * Utility class to create common functions.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Functions
{
    private static $IDENTITY;
    private static $TO_STRING;

    private function __construct()
    {
    }

    public static function init() : void
    {
        self::$IDENTITY = function ($input) {
            return $input;
        };
        self::$TO_STRING = function ($input) {
            return (string) $input;
        };
    }

    /**
     * Creates a function that returns value for any input.
     *
     * @param $value
     * @return callable
     */
    public static function constant($value) : callable
    {
        return function () use ($value) {
            return $value;
        };
    }

    /**
     * Returns the identity function.
     *
     * @return callable
     */
    public static function identity() : callable
    {
        return self::$IDENTITY;
    }

    /**
     * Returns a function that casts its argument to string.
     *
     * @return callable
     */
    public static function toStringFunction() : callable
    {
        return self::$TO_STRING;
    }

    /**
     * Returns the composition of two functions.
     *
     * For f: A->B and g: B->C, composition is defined as the function h such that h(a) == g(f(a)) for each a.
     *
     * @param callable $g
     * @param callable $f
     * @return callable
     */
    public static function compose(callable $g, callable $f) : callable
    {
        return function ($input) use ($g, $f) {
            return Functions::call($g, Functions::call($f, $input));
        };
    }

    /**
     * Returns a function which performs a map lookup.
     *
     * @param array $map
     * @return callable
     * @throws \InvalidArgumentException if given a key that does not exist in the map
     */
    public static function forMap(array $map) : callable
    {
        return function ($index) use ($map) {
            Preconditions::checkArgument(
                array_key_exists($index, $map),
                "The given key '%s' does not exist in the map",
                $index
            );
            return $map[$index];
        };
    }

    /**
     * Returns a function which performs a map lookup with a default value.
     *
     * @param array $map source map that determines the function behavior
     * @param mixed $default the value to return for inputs that aren't map keys
     * @return callable
     */
    public static function forMapOr(array $map, $default) : callable
    {
        return function ($index) use ($map, $default) {
            return array_key_exists($index, $map)
                ? $map[$index]
                : $default;
        };
    }

    /**
     * @param callable $function
     * @param $params
     * @return mixed
     */
    public static function call(callable $function, $params = null)
    {
        $args = func_get_args();
        array_shift($args);
        return call_user_func_array($function, $args);
    }
}
Functions::init();
