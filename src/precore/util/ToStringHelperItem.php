<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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

use DateTime;
use ErrorException;
use precore\lang\Object;

/**
 * Class ToStringHelperItem
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ToStringHelperItem extends Object
{
    private $key;
    private $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    private static function valueToString($value)
    {
        $stringValue = null;
        if ($value === null) {
            $stringValue = 'null';
        } elseif ($value instanceof DateTime) {
            $stringValue = $value->format(DateTime::ISO8601);
        } elseif (is_array($value)) {
            $parts = [];
            foreach ($value as $key => $valueItem) {
                $parts[] = $key . '=' . self::valueToString($valueItem);
            }
            $stringValue = sprintf('[%s]', implode(', ', $parts));
        } else {
            try {
                $stringValue = (string) $value;
            } catch (ErrorException $e) {
                $stringValue = spl_object_hash($value);
            }
        }
        return $stringValue;
    }

    /**
     * @return string|null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function toString()
    {
        $valueString = self::valueToString($this->value);
        return $this->key === null
            ? $valueString
            : $this->key . '=' . $valueString;
    }
}