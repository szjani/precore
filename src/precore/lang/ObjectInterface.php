<?php
declare(strict_types=1);

namespace precore\lang;

use lf4php\Logger;

/**
 * Do NOT implement this interface. You should use it to extend your interfaces.
 * You should use BaseObject class as a base class.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface ObjectInterface
{
    /**
     * @return ObjectClass
     */
    public static function objectClass() : ObjectClass;

    /**
     * @return ObjectClass
     */
    public function getObjectClass() : ObjectClass;

    /**
     * Retrieves the class name.
     *
     * @return string
     */
    public static function className() : string;

    /**
     * @return string
     */
    public function getClassName() : string;

    /**
     * @return string
     */
    public function hashCode() : string;

    /**
     * @param ObjectInterface $object
     * @return boolean
     */
    public function equals(ObjectInterface $object = null) : bool;

    /**
     * @return Logger
     */
    public static function getLogger() : Logger;

    /**
     * @return string
     */
    public function toString() : string;

    /**
     * @return string
     */
    public function __toString() : string;
}
