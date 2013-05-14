<?php
/*
 * Copyright (c) 2012 Szurovecz János
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

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/NotPsr0Class.php';
require_once __DIR__ . '/Psr0Class.php';

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
class ObjectClassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testGetResourceBuiltinClass()
    {
        $objectClass = new ObjectClass('stdClass');
        $objectClass->getResource('anything');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNotPsr0Class()
    {
        $objectClass = new ObjectClass('\invalid\NotPsr0Class');
        $objectClass->getResource('anything');
    }

    public function testGetResourceAbsolute()
    {
        $objectClass = new ObjectClass(Psr0Class::className());
        $filePath = $objectClass->getResource('/resources/res1.resource');
        self::assertFileExists($filePath);
    }

    public function testGetResourceRelative()
    {
        $objectClass = new ObjectClass(Psr0Class::className());
        $filePath = $objectClass->getResource('res2.resource');
        self::assertFileExists($filePath);
    }

    public function testGetMissingResource()
    {
        $objectClass = new ObjectClass(Psr0Class::className());
        $filePath = $objectClass->getResource('res3.resource');
        self::assertNull($filePath);
    }

    public function testCast()
    {
        $objectClass = new ObjectClass(Object::className());
        $object = new Psr0Class();
        self::assertSame($object, $objectClass->cast($object));
    }

    /**
     * @expectedException precore\lang\ClassCastException
     */
    public function testCastException()
    {
        $objectClass = new ObjectClass(Object::className());
        $objectClass->cast($this);
    }
}
