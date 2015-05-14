<?php
/*
 * Copyright (c) 2012 Janos Szurovecz
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

namespace precore\lang;

use lf4php\Logger;

/**
 * Do NOT implement this interface. You should use it to extend your interfaces.
 * You should use Object class as a base class.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface ObjectInterface
{
    /**
     * @return ObjectClass
     */
    public static function objectClass();

    /**
     * @return ObjectClass
     */
    public function getObjectClass();

    /**
     * Retrieves the class name.
     *
     * @return string
     */
    public static function className();

    /**
     * @return string
     */
    public function getClassName();

    /**
     * @return string
     */
    public function hashCode();

    /**
     * @param ObjectInterface $object
     * @return boolean
     */
    public function equals(ObjectInterface $object = null);

    /**
     * @return Logger
     */
    public static function getLogger();

    /**
     * @return string
     */
    public function toString();

    /**
     * @return string
     */
    public function __toString();
}
