<?php
declare(strict_types=1);

namespace precore\lang;

use lf4php\Logger;
use lf4php\LoggerFactory;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class BaseObject implements ObjectInterface
{
    /**
     * @return ObjectClass
     */
    final public static function objectClass() : ObjectClass
    {
        return ObjectClass::forName(static::class);
    }

    /**
     * @return ObjectClass
     */
    final public function getObjectClass() : ObjectClass
    {
        return self::objectClass();
    }

    /**
     * Retrieves the class name.
     *
     * @return string
     */
    final public static function className() : string
    {
        return static::class;
    }

    final public function getClassName() : string
    {
        return static::class;
    }

    /**
     * @return string
     */
    public function hashCode() : string
    {
        return spl_object_hash($this);
    }

    /**
     * @param ObjectInterface $object
     * @return boolean
     */
    public function equals(ObjectInterface $object = null) : bool
    {
        return $this === $object;
    }

    /**
     * @return Logger
     */
    public static function getLogger() : Logger
    {
        return LoggerFactory::getLogger(static::class);
    }

    /**
     * @return string
     */
    public function toString() : string
    {
        return $this->getClassName() . '@' . $this->hashCode();
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->toString();
    }
}
