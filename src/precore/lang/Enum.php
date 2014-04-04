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
 * Initializing objects can be done in constructor, which is recommended to defined as private or protected.
 * Constructor parameters (if any) can be defined by constructorArgs() static method.
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class Enum extends Object
{
    private static $cache = array();

    private $name;

    /**
     * Override if you want to pass any arguments to the constructor defined in your class.
     *
     * @return array Keys must be the names, values are arrays storing constructor parameters
     *
     * For example:
     *
     * class Color extends Enum
     * {
     *     public static $RED;
     *
     *     private $hexValue;
     *
     *     protected function __construct($hexValue)
     *     {
     *         $this->hexValue = $hexValue;
     *     }
     *
     *     protected static function constructorArgs()
     *     {
     *         return array('RED' => array('#ff0000'));
     *     }
     * }
     */
    protected static function constructorArgs()
    {
        return array();
    }

    private static function newInstance($name, array $constructorArgs)
    {
        $reflectionClass = static::objectClass();
        $obj = $reflectionClass->newInstanceWithoutConstructor();
        $obj->name = $name;
        $constructor = $reflectionClass->getConstructor();
        if ($constructor !== null) {
            $constructor->setAccessible(true);
            $numOfParams = $constructor->getNumberOfParameters();
            if ($numOfParams == 0) {
                $constructor->invoke($obj);
            } else {
                if (!array_key_exists($name, $constructorArgs) || !is_array($constructorArgs[$name])
                    || $numOfParams !== count($constructorArgs[$name])) {
                    throw new InvalidArgumentException(
                        sprintf('Invalid arguments are provided for constructor in %s:$%s', static::className(), $name)
                    );
                }
                $constructor->invokeArgs($obj, $constructorArgs[$name]);
            }
        }
        return $obj;
    }

    /**
     * Must be called after your class definition!
     */
    public static function init()
    {
        $className = static::className();
        self::$cache[$className] = array();
        $reflectionClass = static::objectClass();
        $constructorParams = static::constructorArgs();
        foreach ($reflectionClass->getStaticProperties() as $name => $value) {
            $property = new ReflectionProperty($className, $name);
            if ($property->isPublic()) {
                $instance = static::newInstance($name, $constructorParams);
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

    public function toString()
    {
        return $this->className() . '::$' . $this->name();
    }
}
