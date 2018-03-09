<?php
declare(strict_types=1);

namespace precore\lang;

use InvalidArgumentException;
use precore\util\Preconditions;
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
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class Enum extends BaseObject implements Comparable
{
    private static $cache = [];
    private static $ordinals = [];

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
        return [];
    }

    /**
     * @param $name
     * @param array $constructorArgs
     * @return static
     */
    private static function newInstance($name, array $constructorArgs) : self
    {
        $reflectionClass = self::objectClass();
        $obj = $reflectionClass->newInstanceWithoutConstructor();
        $obj->name = $name;
        $constructor = $reflectionClass->getConstructor();
        if ($constructor !== null) {
            $constructor->setAccessible(true);
            $numOfParams = $constructor->getNumberOfParameters();
            if ($numOfParams == 0) {
                $constructor->invoke($obj);
            } else {
                Preconditions::checkArgument(
                    array_key_exists($name, $constructorArgs) && is_array($constructorArgs[$name])
                        && $numOfParams === count($constructorArgs[$name]),
                    'Invalid arguments are provided for constructor in %s:$%s',
                    static::class,
                    $name
                );
                $constructor->invokeArgs($obj, $constructorArgs[$name]);
            }
        }
        return $obj;
    }

    /**
     * Must be called after your class definition!
     */
    final public static function init() : void
    {
        $className = static::class;
        self::$cache[$className] = [];
        $reflectionClass = self::objectClass();
        $constructorParams = static::constructorArgs();
        $ordinal = 0;
        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_STATIC) as $property) {
            if ($property->isPublic()) {
                $name = $property->getName();
                $instance = self::newInstance($name, $constructorParams);
                $property->setValue($instance);
                self::$cache[$className][$name] = $instance;
                self::$ordinals[$className][$name] = $ordinal++;
            }
        }
    }

    /**
     * You can obtain the appropriate instance by their name.
     *
     * @param string $name
     * @return static
     * @throws InvalidArgumentException
     */
    final public static function valueOf($name) : self
    {
        $className = static::class;
        Preconditions::checkArgument(
            array_key_exists($className, self::$cache) && array_key_exists($name, self::$cache[$className]),
            "The enum '%s' type has no constant with name '%s'",
            $className,
            $name
        );
        return self::$cache[$className][$name];
    }

    /**
     * @return static[]
     */
    final public static function values() : array
    {
        $className = static::class;
        return array_key_exists($className, self::$cache)
            ? self::$cache[$className]
            : [];
    }

    /**
     * The type and the name equality is being checked. Although the reference should be the same,
     * it can differ if one of the two objects have been deserialized.
     *
     * @param ObjectInterface $object
     * @return bool
     */
    public function equals(ObjectInterface $object = null) : bool
    {
        return $object instanceof static && $object->name === $this->name;
    }

    /**
     * Returns the name of this enum constant, exactly as declared in its enum declaration.
     * Most programmers should use the toString() method in preference to this one, as the toString method
     * may return a more user-friendly name. This method is designed primarily for use in specialized situations
     * where correctness depends on getting the exact name, which will not vary from release to release.
     *
     * @return string
     */
    final public function name() : string
    {
        return $this->name;
    }

    /**
     * Returns the ordinal of this enumeration constant
     * (its position in its enum declaration, where the initial constant is assigned an ordinal of zero).
     * Most programmers will have no use for this method.
     *
     * @return int
     */
    final public function ordinal() : int
    {
        return self::$ordinals[$this->getClassName()][$this->name()];
    }

    /**
     * Returns the name of this enum constant, as contained in the declaration. This method may be overridden,
     * though it typically isn't necessary or desirable. An enum type should override this method
     * when a more "programmer-friendly" string form exists.
     *
     * @return string
     */
    public function toString() : string
    {
        return $this->name;
    }

    /**
     * @param $object
     * @return int a negative integer, zero, or a positive integer
     *         as this object is less than, equal to, or greater than the specified object.
     * @throws ClassCastException - if the specified object's type prevents it from being compared to this object.
     * @throws NullPointerException if the specified object is null
     */
    public function compareTo(?object $object) : int
    {
        Preconditions::checkNotNull($object, 'The given object is null');
        if (!($object instanceof static)) {
            throw new ClassCastException('The given object is not instance of this class');
        }
        /* @var $object Enum */
        return $this->ordinal() - $object->ordinal();
    }
}
