<?php
declare(strict_types=1);

namespace precore\lang;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/NotPsr0Class.php';
require_once __DIR__ . '/Psr0Class.php';

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ObjectClassTest extends TestCase
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
        $objectClass = new ObjectClass(Psr0Class::class);
        $filePath = $objectClass->getResource('/resources/res1.resource');
        self::assertFileExists($filePath);
    }

    public function testGetResourceRelative()
    {
        $objectClass = new ObjectClass(Psr0Class::class);
        $filePath = $objectClass->getResource('res2.resource');
        self::assertFileExists($filePath);
    }

    public function testGetMissingResource()
    {
        $objectClass = new ObjectClass(Psr0Class::class);
        $filePath = $objectClass->getResource('res3.resource');
        self::assertNull($filePath);
    }

    /**
     * @test
     */
    public function shouldCastNullToEverything()
    {
        $res = BaseObject::objectClass()->cast(null);
        self::assertNull($res);
    }

    public function testCast()
    {
        $objectClass = new ObjectClass(BaseObject::class);
        $object = new Psr0Class();
        self::assertSame($object, $objectClass->cast($object));
    }

    /**
     * @expectedException \precore\lang\ClassCastException
     */
    public function testCastException()
    {
        $objectClass = new ObjectClass(BaseObject::class);
        $objectClass->cast($this);
    }

    public function testNewsInstanceWithoutConstructor()
    {
        $obj = Psr0Class::objectClass()->newInstanceWithoutConstructor();
        self::assertInstanceOf(Psr0Class::class, $obj);
    }

    public function testForName()
    {
        $class1 = ObjectClass::forName(BaseObject::class);
        $class2 = BaseObject::objectClass();
        self::assertSame($class2, $class1);
    }

    public function testSlash()
    {
        $class1 = ObjectClass::forName('\precore\lang\BaseObject');
        $class2 = ObjectClass::forName('precore\lang\BaseObject');
        self::assertSame($class1, $class2);
    }

    public function testIsAssignableFrom()
    {
        $thisClass = ObjectClass::forName(__CLASS__);
        $parentClass = ObjectClass::forName("PHPUnit\Framework\TestCase");
        self::assertTrue($parentClass->isAssignableFrom($thisClass));
        self::assertTrue($parentClass->isAssignableFrom($parentClass));
        self::assertTrue($thisClass->isAssignableFrom($thisClass));
        self::assertFalse($thisClass->isAssignableFrom($parentClass));
        $interfaceClass = ObjectClass::forName('\precore\lang\ObjectInterface');
        self::assertTrue($interfaceClass->isAssignableFrom(ObjectClass::forName('\precore\lang\BaseObject')));
        self::assertTrue($interfaceClass->isAssignableFrom($interfaceClass));
    }
}
