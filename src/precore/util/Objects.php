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

use precore\lang\Object;
use precore\lang\ObjectInterface;

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
        if ($objA === null && $objB === null) {
            return true;
        }
        if ($objA instanceof ObjectInterface && $objB instanceof ObjectInterface) {
            return $objA->equals($objB);
        }
        return $objA == $objB;
    }
}
