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
    public static function init()
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
    public static function forName($className)
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
    public function isPsr0Compatible()
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
    public function getResource($resource)
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
     *
     * @param object $object
     * @return object $object itself
     * @throws ClassCastException
     */
    public function cast($object)
    {
        if (!$this->isInstance($object)) {
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
    public function isAssignableFrom(ReflectionClass $class)
    {
        return $this->getName() == $class->getName() || $class->isSubclassOf($this->getName());
    }
}
ObjectClass::init();
