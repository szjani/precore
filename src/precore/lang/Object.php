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
use lf4php\LoggerFactory;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class Object implements ObjectInterface
{
    /**
     * @return ObjectClass
     */
    final public static function objectClass()
    {
        return ObjectClass::forName(static::className());
    }

    /**
     * @return ObjectClass
     */
    final public function getObjectClass()
    {
        return static::objectClass();
    }

    /**
     * Retrieves the class name.
     *
     * @return string
     */
    final public static function className()
    {
        return get_called_class();
    }

    final public function getClassName()
    {
        return static::className();
    }

    /**
     * @return string
     */
    public function hashCode()
    {
        return spl_object_hash($this);
    }

    /**
     * @param ObjectInterface $object
     * @return boolean
     */
    public function equals(ObjectInterface $object = null)
    {
        return $this === $object;
    }

    /**
     * @return Logger
     */
    public static function getLogger()
    {
        return LoggerFactory::getLogger(static::className());
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->getClassName() . '@' . $this->hashCode();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
