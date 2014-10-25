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

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/NotPsr0Class.php';
require_once __DIR__ . '/Psr0Class.php';

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ObjectClassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage This method cannot be called for built-in classes!
     */
    public function testGetResourceBuiltinClass()
    {
        $objectClass = new ObjectClass('stdClass');
        $objectClass->getResource('anything');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage This method cannot be called for built-in classes!
     */
    public function shouldIsPsr0ThrowExceptionOnBuiltinClass()
    {
        $objectClass = new ObjectClass('stdClass');
        self::assertFalse($objectClass->isPsr0Compatible());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Class 'invalid\NotPsr0Class' must be PSR-0 compatible!
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
     * @expectedException \precore\lang\ClassCastException
     */
    public function testCastException()
    {
        $objectClass = new ObjectClass(Object::className());
        $objectClass->cast($this);
    }

    public function testNewsInstanceWithoutConstructor()
    {
        $obj = Psr0Class::objectClass()->newInstanceWithoutConstructor();
        self::assertInstanceOf(Psr0Class::className(), $obj);
    }

    public function testForName()
    {
        $class1 = ObjectClass::forName(Object::className());
        $class2 = Object::objectClass();
        self::assertSame($class2, $class1);
    }

    public function testSlash()
    {
        $class1 = ObjectClass::forName('\precore\lang\Object');
        $class2 = ObjectClass::forName('precore\lang\Object');
        self::assertSame($class1, $class2);
    }

    public function testIsAssignableFrom()
    {
        $thisClass = ObjectClass::forName(__CLASS__);
        $parentClass = ObjectClass::forName("PHPUnit_Framework_TestCase");
        self::assertTrue($parentClass->isAssignableFrom($thisClass));
        self::assertTrue($parentClass->isAssignableFrom($parentClass));
        self::assertTrue($thisClass->isAssignableFrom($thisClass));
        self::assertFalse($thisClass->isAssignableFrom($parentClass));
        $interfaceClass = ObjectClass::forName('\precore\lang\ObjectInterface');
        self::assertTrue($interfaceClass->isAssignableFrom(ObjectClass::forName('\precore\lang\Object')));
        self::assertTrue($interfaceClass->isAssignableFrom($interfaceClass));
    }
}
