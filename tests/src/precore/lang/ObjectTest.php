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

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ObjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SampleObj
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new SampleObj();
    }

    public function testGetClassName()
    {
        self::assertSame(__NAMESPACE__ . '\SampleObject', $this->obj->getClassName());
    }

    public function testClassName()
    {
        self::assertSame(__NAMESPACE__ . '\SampleObject', SampleObj::className());
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
        $obj2 = new SampleObj();
        self::assertFalse($this->obj->equals($obj2));
    }

    public function testObjectClass()
    {
        self::assertEquals(SampleObj2::className(), SampleObj2::objectClass()->getName());
        self::assertEquals(SampleObj::className(), SampleObj::objectClass()->getName());
        self::assertSame(SampleObj::objectClass(), SampleObj::objectClass());
    }

    public function testGetObjectClass()
    {
        $object = new SampleObj();
        self::assertEquals(SampleObj::objectClass()->getName(), $object->getObjectClass()->getName());
    }

    public function testGetLogger()
    {
        self::assertInstanceOf('\lf4php\Logger', SampleObj::getLogger());
    }
}

class SampleObj extends Obj
{
    private $id;
}

class SampleObj2 extends Obj
{
    private $id2;
}
