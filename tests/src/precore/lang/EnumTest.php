<?php
declare(strict_types=1);

namespace precore\lang;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

require_once 'Color.php';
require_once 'Animal.php';
require_once 'EmptyEnum.php';
require_once 'EmptyConstructorEnum.php';
require_once 'MissingConstructorArgs.php';

/**
 * Description of EnumTest
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class EnumTest extends TestCase
{
    public function testCreate()
    {
        $red = Color::$RED;
        self::assertInstanceOf(Color::class, $red);
        self::assertEquals('RED', $red->name());
        self::assertTrue($red->equals(Color::valueOf('RED')));
        self::assertEquals(2, count(Color::values()));
    }

    /**
     * @test
     */
    public function shouldCheckClassAndNameForEquals()
    {
        self::assertFalse(Color::$RED->equals(Color2::$RED));
    }

    public function testValues()
    {
        $colors = Color::values();
        $animals = Animal::values();
        self::assertContains(Color::$RED, $colors);
        self::assertContains(Animal::$CAT, $animals);
        self::assertEquals(2, count($colors));
        self::assertEquals(3, count($animals));
        self::assertEquals(0, count(EmptyEnum::values()));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidValueOf()
    {
        Color::valueOf('invalid');
    }

    public function testConstructorCall()
    {
        self::assertEquals(Color::BLUE_HEX, Color::$BLUE->getHexCode());
        self::assertEquals(EmptyConstructorEnum::VALUE, EmptyConstructorEnum::$ITEM1->getValue());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidConstructor()
    {
        MissingConstructorArgs::init();
    }

    public function testOrdinal()
    {
        self::assertEquals(0, Color::$RED->ordinal());
        self::assertEquals(1, Color::$BLUE->ordinal());
        self::assertGreaterThan(0, Color::$BLUE->compareTo(Color::$RED));
        self::assertLessThan(0, Color::$RED->compareTo(Color::$BLUE));

        self::assertEquals(0, Animal::$DOG->ordinal());
        self::assertEquals(1, Animal::$CAT->ordinal());
        self::assertEquals(2, Animal::$HORSE->ordinal());
    }

    /**
     * @test
     * @expectedException \precore\lang\ClassCastException
     */
    public function shouldFailIfNotTheSameInstancesAreCompared()
    {
        Color::$BLUE->compareTo(Animal::$DOG);
    }

    public function testSerialization()
    {
        $obj = Animal::$DOG;
        $ser = serialize($obj);
        $ret = unserialize($ser);
        self::assertEquals($obj->name(), $ret->name());
        self::assertEquals($obj->ordinal(), $ret->ordinal());
    }
}

class Color2 extends Enum
{
    public static $RED;
}
Color2::init();
