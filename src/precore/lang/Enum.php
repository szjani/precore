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

use InvalidArgumentException;
use ReflectionProperty;

/**
 * Extending this class will simulate an enum known in several languages.
 * The following rules must be followed:
 *  - use public static variables
 *  - call init() method right after the class definition
 *
 * All public static variables will be an instance of your class.
 * The instances stores the given name as a string
 * which can be obtained through name() method.
 *
 * For example:
 *
 * <pre>
 * class Color extends Enum
 * {
 *     public static $RED;
 *     public static $BLUE;
 * }
 * Color::init();
 * </pre>
 *
 * In this case Color::$RED will be a Color instance and its name() method will return 'RED'.
 *
 * All public static variables declared in your class will be used as a possible enum constant!
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class Enum extends Object
{
    protected static $cache = array();

    private $name;

    private function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Must be called after your class definition!
     */
    public static function init()
    {
        $className = static::className();
        self::$cache[$className] = array();
        foreach (static::objectClass()->getStaticProperties() as $name => $value) {
            $property = new ReflectionProperty($className, $name);
            if ($property->isPublic()) {
                $instance = new static($name);
                static::objectClass()->setStaticPropertyValue($name, $instance);
                self::$cache[$className][$name] = $instance;
            }
        }
    }

    /**
     * You can obtain the appropriate instance by their name.
     *
     * @param string $name
     * @return Enum
     * @throws InvalidArgumentException
     */
    public static function valueOf($name)
    {
        $className = static::className();
        if (!array_key_exists($className, self::$cache) || !array_key_exists($name, self::$cache[$className])) {
            throw new InvalidArgumentException("The enum '$className' type has no constant with name '$name'");
        }
        return self::$cache[$className][$name];
    }

    public static function values()
    {
        return array_key_exists(static::className(), self::$cache)
            ? self::$cache[static::className()]
            : array();
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof static && $object->name === $this->name;
    }

    public function name()
    {
        return $this->name;
    }
}
