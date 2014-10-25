<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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

use InvalidArgumentException;
use OutOfBoundsException;
use precore\lang\IllegalStateException;
use precore\lang\NullPointerException;

/**
 * Static convenience methods that help a method or constructor check whether it was invoked
 * correctly (whether its <i>preconditions</i> have been met). These methods generally accept a
 * {@code boolean} expression which is expected to be {@code true} (or in the case of {@code
 * checkNotNull}, an object reference which is expected to be non-null). When {@code false} (or
 * {@code null}) is passed instead, the {@code Preconditions} method throws a runtime exception,
 * which helps the calling method communicate to <i>its</i> caller that <i>that</i> caller has made
 * a mistake.
 *
 * <p> {@code vsprintf} is used to create message string. The parameters are varargs in all methods.
 *
 * @see https://code.google.com/p/guava-libraries/wiki/PreconditionsExplained
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Preconditions
{
    private function __construct()
    {
    }

    /**
     * @param boolean $expression
     * @param string $errorMessage
     * @param string... $param
     * @throws InvalidArgumentException
     */
    public static function checkArgument($expression, $errorMessage = null, $params = null)
    {
        if (!$expression) {
            throw new InvalidArgumentException(self::format($errorMessage, func_get_args()));
        }
    }

    /**
     * @param boolean $expression
     * @param string $errorMessage
     * @param string... $param
     * @throws IllegalStateException
     */
    public static function checkState($expression, $errorMessage = null, $params = null)
    {
        if (!$expression) {
            throw new IllegalStateException(self::format($errorMessage, func_get_args()));
        }
    }

    /**
     * @param mixed $object
     * @param string $errorMessage
     * @param string... $param
     * @throws NullPointerException
     * @return mixed the $object itself if not null
     */
    public static function checkNotNull($object, $errorMessage = null, $params = null)
    {
        if ($object === null) {
            throw new NullPointerException(self::format($errorMessage, func_get_args()));
        }
        return $object;
    }

    /**
     * @param array $array
     * @param string $key
     * @param string $errorMessage
     * @param string... $params
     * @return mixed the element with the given index
     * @throws OutOfBoundsException
     */
    public static function checkElementExists(array $array, $key, $errorMessage = null, $params = null)
    {
        if (!array_key_exists($key, $array)) {
            throw new OutOfBoundsException(self::format($errorMessage, func_get_args(), 3));
        }
        return $array[$key];
    }

    private static function format($errorMessage, $functionParams, $offset = 2)
    {
        return vsprintf($errorMessage, array_slice($functionParams, $offset));
    }
}
