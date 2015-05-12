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

/**
 * Utility class to create common functions.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Functions
{
    private function __construct()
    {
    }

    /**
     * Creates a function that returns value for any input.
     *
     * @param $value
     * @return callable
     */
    public static function constant($value)
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
    public static function identity()
    {
        return function ($input) {
            return $input;
        };
    }

    /**
     * Returns a function that casts its argument to string.
     *
     * @return callable
     */
    public static function toStringFunction()
    {
        return function ($input) {
            return (string) $input;
        };
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
    public static function compose(callable $g, callable $f)
    {
        return function ($input) use ($g, $f) {
            return call_user_func($g, call_user_func($f, $input));
        };
    }

    /**
     * Returns a function which performs a map lookup.
     *
     * @param array $map
     * @return callable
     * @throws \InvalidArgumentException if given a key that does not exist in the map
     */
    public static function forMap(array $map)
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
    public static function forMapOr(array $map, $default)
    {
        return function ($index) use ($map, $default) {
            return array_key_exists($index, $map)
                ? $map[$index]
                : $default;
        };
    }
}
