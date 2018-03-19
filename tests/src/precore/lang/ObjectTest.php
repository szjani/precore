<?php
declare(strict_types=1);

namespace precore\lang;

use PHPUnit\Framework\TestCase;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ObjectTest extends TestCase
{
    /**
     * @var SampleObject
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new SampleObject();
    }

    public function testGetClassName()
    {
        self::assertSame(__NAMESPACE__ . '\SampleObject', $this->obj->getClassName());
    }

    public function testClassName()
    {
        self::assertSame(__NAMESPACE__ . '\SampleObject', SampleObject::class);
    }

    public function testHashCode()
    {
        self::assertSame(spl_object_hash($this->obj), $this->obj->hashCode());
    }

    public function testToString()
    {
        self::assertSame(__NAMESPACE__ . '\SampleObject@' . spl_object_hash($this->obj), $this->obj->toString());
    }

    public function test__toString()
    {
        self::assertSame($this->obj->toString(), (string) $this->obj);
    }

    public function testEquals()
    {
        self::assertFalse($this->obj->equals(null));
        $obj2 = new SampleObject();
        self::assertFalse($this->obj->equals($obj2));
    }

    public function testObjectClass()
    {
        self::assertEquals(SampleObject2::class, SampleObject2::objectClass()->getName());
        self::assertEquals(SampleObject::class, SampleObject::objectClass()->getName());
        self::assertSame(SampleObject::objectClass(), SampleObject::objectClass());
    }

    public function testGetObjectClass()
    {
        $object = new SampleObject();
        self::assertEquals(SampleObject::objectClass()->getName(), $object->getObjectClass()->getName());
    }

    public function testGetLogger()
    {
        self::assertInstanceOf('\lf4php\Logger', SampleObject::getLogger());
    }
}

class SampleObject extends BaseObject
{
    private $id;
}

class SampleObject2 extends BaseObject
{
    private $id2;
}
