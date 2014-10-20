<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
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
use precore\lang\Object;
use precore\lang\ObjectInterface;
use ReflectionClass;

/**
 * Helper functions that can operate on any ObjectInterface.
 *
 * @package precore\util
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
final class Objects extends Object
{
    private function __construct()
    {
    }

    /**
     * Determines whether two possibly-null objects are equal. Returns:
     *
     *  - True, if $objA and $objB are both null
     *  - True, if $objA and $objB are both non-null,
     *    $objA and $objB are both ObjectInterface instances,
     *    and $objA->equals($objB) is true
     *  - True, if $objA == $objB
     *  - false in all other situations.
     *
     * @param $objA
     * @param $objB
     * @return boolean
     */
    public static function equal($objA, $objB)
    {
        if ($objA === $objB) {
            return true;
        }
        if ($objA instanceof ObjectInterface && $objB instanceof ObjectInterface) {
            return $objA->equals($objB);
        }
        return $objA == $objB;
    }

    /**
     * Creates an instance of ToStringHelper.
     * This is helpful for implementing ObjectInterface::toString().
     *
     * @param $identifier
     * @throws \InvalidArgumentException
     * @return ToStringHelper
     */
    public static function toStringHelper($identifier)
    {
        Preconditions::checkArgument(
            is_object($identifier) || is_string($identifier),
            'An object, a string, or a ReflectionClass must be used as identifier'
        );
        $name = null;
        if ($identifier instanceof ReflectionClass) {
            $name = $identifier->getName();
        } elseif (is_object($identifier)) {
            $name = get_class($identifier);
        } elseif (is_string($identifier)) {
            $name = $identifier;
        }
        return new ToStringHelper($name);
    }
}
