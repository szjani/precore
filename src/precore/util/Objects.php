<?php
declare(strict_types=1);

namespace precore\util;

use precore\lang\BaseObject;
use precore\lang\ObjectInterface;
use ReflectionClass;

/**
 * Helper functions that can operate on any ObjectInterface.
 *
 * @package precore\util
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Objects extends BaseObject
{
    private function __construct()
    {
    }

    /**
     * Determines whether two possibly-null objects are equal. Returns:
     *
     *  - True, if $objA and $objB are both null
     *  - True, if $objA and $objB are both non-null,
     *    $objA and $objB are both ObjectInterface instances,
     *    and $objA->equals($objB) is true
     *  - True, if $objA == $objB
     *  - false in all other situations.
     *
     * @param $objA
     * @param $objB
     * @return boolean
     */
    public static function equal($objA, $objB) : bool
    {
        if ($objA === $objB) {
            return true;
        }
        if ($objA instanceof ObjectInterface && $objB instanceof ObjectInterface) {
            return $objA->equals($objB);
        }
        return $objA == $objB;
    }

    /**
     * Creates an instance of ToStringHelper.
     * This is helpful for implementing ObjectInterface::toString().
     *
     * @param $identifier
     * @throws \InvalidArgumentException
     * @return ToStringHelper
     */
    public static function toStringHelper($identifier) : ToStringHelper
    {
        Preconditions::checkArgument(
            is_object($identifier) || is_string($identifier),
            'An object, a string, or a ReflectionClass must be used as identifier'
        );
        $name = null;
        if ($identifier instanceof ReflectionClass) {
            $name = $identifier->getName();
        } elseif (is_object($identifier)) {
            $name = get_class($identifier);
        } elseif (is_string($identifier)) {
            $name = $identifier;
        }
        return new ToStringHelper($name);
    }
}
