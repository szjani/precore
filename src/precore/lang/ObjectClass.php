<?php
declare(strict_types=1);

namespace precore\lang;

use LazyMap\CallbackLazyMap;
use precore\util\Preconditions;
use ReflectionClass;
use RuntimeException;

/**
 * Extends ReflectionClass with some new features.
 * It caches the instances if you obtain them through ObjectClass::forName().
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ObjectClass extends ReflectionClass
{
    /**
     * @var CallbackLazyMap
     */
    private static $classMap;

    /**
     * Do not call from your code, workaround to initialize static variable.
     */
    public static function init() : void
    {
        self::$classMap = new CallbackLazyMap(
            function ($className) {
                $trimmedClassName = trim($className, '\\');
                return $trimmedClassName === $className
                    ? new ObjectClass($className)
                    : ObjectClass::$classMap->$trimmedClassName;
            }
        );
    }

    /**
     * @param string $className FQCN
     * @return ObjectClass
     */
    public static function forName($className) : ObjectClass
    {
        return self::$classMap->$className;
    }

    protected function getSlashedFileName()
    {
        $classFileName = $this->getFileName();
        Preconditions::checkState($classFileName !== false, 'This method cannot be called for built-in classes!');
        return str_replace('\\', '/', $classFileName);
    }

    protected function getSlashedName()
    {
        return str_replace('\\', '/', $this->getName());
    }

    /**
     * Whether the class name is PSR-0 compatible or not.
     *
     * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
     * @return boolean
     */
    public function isPsr0Compatible() : bool
    {
        return preg_match("#{$this->getSlashedName()}.php$#", $this->getSlashedFileName()) !== 0;
    }

    /**
     * Get a file name depending on current class.
     *
     * Suppose the class is \foo\Bar which located in src/foo/Bar.php
     * Absolute path: /resources/res1 must be located in src/resources/res1
     * Relative path: resources/res2 must be located in src/foo/resources/res2
     *
     * @param string $resource
     * @return string File path of $resource or null if not exists
     * @see java.lang.Class
     * @throws RuntimeException Class is built-int
     * @throws IllegalStateException Class is not PSR-0 compatible
     */
    public function getResource($resource) : ?string
    {
        Preconditions::checkState($this->isPsr0Compatible(), "Class '%s' must be PSR-0 compatible!", $this->getName());
        $slashedFileName = $this->getSlashedFileName();
        $filePath = $resource[0] == '/'
            ? str_replace("/{$this->getSlashedName()}.php", '', $slashedFileName) . $resource
            : dirname($slashedFileName) . '/' . $resource;
        return is_file($filePath) ? $filePath : null;
    }

    /**
     * Check whether $object can be cast to $this->getName().
     * Note, that null can be cast to anything. In other words, it returns null if the given object is null.
     *
     * @param object $object
     * @return object $object itself
     * @throws ClassCastException
     */
    public function cast(?object $object) : ?object
    {
        if ($object !== null && !$this->isInstance($object)) {
            $objectClass = get_class($object);
            throw new ClassCastException("'{$objectClass}' cannot be cast to '{$this->getName()}'");
        }
        return $object;
    }

    /**
     * Determines if the class or interface represented by this ObjectClass object is either the same as,
     * or is a superclass or superinterface of, the class or interface
     * represented by the specified ObjectClass parameter. It returns true if so; otherwise it returns false.
     *
     * @param ReflectionClass $class
     * @return bool
     */
    public function isAssignableFrom(ReflectionClass $class) : bool
    {
        return $this->getName() == $class->getName() || $class->isSubclassOf($this->getName());
    }
}
ObjectClass::init();
