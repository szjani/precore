<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;
use precore\lang\BaseObject;
use precore\lang\ObjectInterface;

class ObjectsTest extends TestCase
{
    /**
     * @test
     */
    public function bothNullEqual()
    {
        self::assertTrue(Objects::equal(null, null));
    }

    /**
     * @test
     */
    public function equalByEquals()
    {
        $string = 'Hello World!';
        $str1 = new StringClass($string);
        $str2 = new StringClass($string);
        self::assertTrue(Objects::equal($str1, $str2));
    }

    /**
     * @test
     */
    public function checkEqualityIfSecondParameterIsNull()
    {
        $string = 'Hello World!';
        $str1 = new StringClass($string);
        self::assertFalse(Objects::equal($str1, null));
    }

    /**
     * @test
     */
    public function scalarEqual()
    {
        self::assertTrue(Objects::equal(1, '1'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @test
     */
    public function invalidToStringHelperIdentifier()
    {
        Objects::toStringHelper(4);
    }

    /**
     * @test
     */
    public function objectBasedToStringHelper()
    {
        $helper = Objects::toStringHelper($this);
        self::assertStringStartsWith(__CLASS__, $helper->toString());
    }

    /**
     * @test
     */
    public function stringBasedToStringHelper()
    {
        $helper = Objects::toStringHelper(__CLASS__);
        self::assertStringStartsWith(__CLASS__, $helper->toString());
    }

    /**
     * @test
     */
    public function reflectionBasedToStringHelper()
    {
        $helper = Objects::toStringHelper(UUID::objectClass());
        self::assertStringStartsWith(UUID::class, $helper->toString());
    }

    /**
     * @test
     */
    public function shouldCheckReferenceFirst()
    {
        $obj = $this->getMockBuilder(StringClass::class)
            ->setMethods(['equals'])
            ->setConstructorArgs(['any'])
            ->getMock();
        $obj
            ->expects(self::never())
            ->method('equals');
        self::assertTrue(Objects::equal($obj, $obj));
    }
}

class StringClass extends BaseObject
{
    private $data;

    public function __construct($data)
    {
        $this->data = (string) $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        if ($object === null) {
            return false;
        }
        if ($object instanceof self) {
            return $this->data === $object->data;
        }
        return false;
    }
}
