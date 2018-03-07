<?php
/*
 * Copyright (c) 2013 Janos Szurovecz
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

namespace precore\util;

use InvalidArgumentException;
use precore\lang\Obj;
use precore\lang\ObjectInterface;
use Serializable;

/**
 * A class that represents an immutable universally unique identifier (UUID).
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class UUID extends Obj implements Serializable
{
    const FULL_PATTERN = '/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i';

    private $value;

    private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @see http://www.php.net/manual/en/function.uniqid.php#94959
     * @return UUID
     */
    public static function randomUUID()
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

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof self && $this->value === $object->value;
    }

    /**
     * @param $string
     * @return UUID
     * @throws InvalidArgumentException if the given parameter is not string or not a well formatted UUID
     */
    public static function fromString($string)
    {
        Preconditions::checkArgument(is_string($string), 'A string must be passed as parameter');
        Preconditions::checkArgument(
            preg_match(self::FULL_PATTERN, $string) == 1,
            "'%s' is not a correct UUID",
            $string
        );
        return new UUID($string);
    }

    public function toString()
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
