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
use Iterator;
use IteratorAggregate;
use precore\lang\Object;

/**
 * Can be used in ObjectInterface implementations for overriding toString() method.
 *
 * @package precore\util
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ToStringHelper extends Object
{
    private $className;

    /**
     * @var ToStringHelperItem[]
     */
    private $values = [];

    private $omitNullValues = false;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    public static function valueToString($value)
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
     * Can be called with 1 or 2 parameters. In the first case only the value,
     * otherwise a key/value pair is passed.
     *
     * @param string $param
     * @return ToStringHelper $this
     */
    public function add($param)
    {
        $args = func_get_args();
        $count = count($args);
        Preconditions::checkArgument($count == 1 || $count == 2);
        $this->values[] = $count == 2
            ? new ToStringHelperItem($args[0], $args[1])
            : new ToStringHelperItem(null, $args[0]);
        return $this;
    }

    /**
     * Configures the ToStringHelper so toString() will ignore properties with null value.
     *
     * @return ToStringHelper $this
     */
    public function omitNullValues()
    {
        $this->omitNullValues = true;
        return $this;
    }

    public function toString()
    {
        return sprintf("%s%s", $this->className, $this->membersToString());
    }

    protected function membersToString()
    {
        $parts = [];
        foreach ($this->values as $item) {
            if ($this->omitNullValues && $item->getValue() === null) {
                continue;
            }
            $parts[] = $item->toString();
        }
        return sprintf('{%s}', implode(', ', $parts));
    }
}
