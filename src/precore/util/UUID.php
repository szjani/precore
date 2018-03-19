<?php
declare(strict_types=1);

namespace precore\util;

use InvalidArgumentException;
use precore\lang\BaseObject;
use precore\lang\ObjectInterface;
use Serializable;

/**
 * A class that represents an immutable universally unique identifier (UUID).
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class UUID extends BaseObject implements Serializable
{
    const FULL_PATTERN = '/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i';

    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @see http://www.php.net/manual/en/function.uniqid.php#94959
     * @return UUID
     */
    public static function randomUUID() : UUID
    {
        $value = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
        return new UUID($value);
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        return $object instanceof self && $this->value === $object->value;
    }

    /**
     * @param $string
     * @return UUID
     * @throws InvalidArgumentException if the given parameter is not string or not a well formatted UUID
     */
    public static function fromString(string $string) : UUID
    {
        Preconditions::checkArgument(
            preg_match(self::FULL_PATTERN, $string) == 1,
            "'%s' is not a correct UUID",
            $string
        );
        return new UUID($string);
    }

    public function toString() : string
    {
        return $this->value;
    }

    public function serialize()
    {
        return serialize($this->value);
    }

    public function unserialize($serialized)
    {
        $this->value = unserialize($serialized);
    }
}
