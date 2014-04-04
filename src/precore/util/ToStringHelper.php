<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
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

use precore\lang\Object;

/**
 * Can be used in ObjectInterface implementations for overriding toString() method.
 *
 * @package precore\util
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class ToStringHelper extends Object
{
    private $className;

    private $values = array();

    private $omitNullValues = false;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * @param $name
     * @param $value
     * @return ToStringHelper $this
     */
    public function add($name, $value)
    {
        $this->values[$name] = $value;
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
        $values = $this->values;
        if ($this->omitNullValues) {
            $values = array_filter(
                $values,
                function ($value) {
                    return $value !== null;
                }
            );
        }
        array_walk(
            $values,
            function (&$item, $key) {
                $value = $item === null ? 'null' : (string) $item;
                $item = $key . '=' . $value;
            }
        );
        return sprintf("%s{%s}", $this->className, implode(', ', $values));
    }
}
