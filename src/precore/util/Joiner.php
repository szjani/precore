<?php
/*
 * Copyright (c) 2012-2015 Janos Szurovecz
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

use ArrayIterator;
use precore\lang\NullPointerException;
use Traversable;

/**
 * An object which joins pieces of text (specified as an array or {@link Traversable}) with a separator.
 * Example:
 * <pre>
 *   $joiner = Joiner::on("; ")->skipNulls();
 *   return $joiner->join(["Harry", null, "Ron", "Hermione"]);
 * </pre>
 *
 * This returns the string "Harry; Ron; Hermione". Note that all input elements are
 * converted to strings using {@link ToStringHelper::valueToString} before being appended.
 *
 * <p>If neither {@link Joiner::skipNulls()} nor {@link Joiner::useForNull()} is specified, the joining
 * methods will throw {@link NullPointerException} if any given element is null.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Joiner
{
    /**
     * @var string
     */
    private $separator;

    /**
     * input element -> output element conversion
     * If the return value is null, it will be omitted from the output.
     *
     * @var callable
     */
    private $converter;

    /**
     * @param $separator
     * @param callable $converter
     */
    private function __construct($separator, callable $converter = null)
    {
        Preconditions::checkArgument(is_string($separator), 'separator must be a string');
        if ($converter === null) {
            $converter = function ($element) {
                if ($element === null) {
                    throw new NullPointerException('An element is null in the given collection');
                };
                return $element;
            };
        }
        $this->separator = $separator;
        $this->converter = $converter;
    }

    /**
     * Returns a joiner which automatically places {@link Joiner::separator} between consecutive elements.
     *
     * @param string $separator
     * @return Joiner
     */
    public static function on($separator)
    {
        return new Joiner($separator);
    }

    /**
     * Returns a joiner with the same behavior as this joiner, except automatically skipping over any
     * provided null elements.
     *
     * @return Joiner
     */
    public function skipNulls()
    {
        return new Joiner($this->separator,
            function ($element) {
                return $element;
            }
        );
    }

    /**
     * Returns a joiner with the same behavior as this one, except automatically substituting $nullText
     * for any provided null elements.
     *
     * @param $nullText
     * @return Joiner
     */
    public function useForNull($nullText)
    {
        Preconditions::checkArgument(is_string($nullText), 'nullText must be a string');
        return new Joiner(
            $this->separator,
            function ($element) use ($nullText) {
                return $element !== null ? $element : $nullText;
            }
        );
    }

    /**
     * Returns a string containing the string representation of each element of $parts, using the previously
     * configured separator between each.
     *
     * @param array|Traversable $parts
     * @return string
     */
    public function join($parts)
    {
        if (is_array($parts)) {
            $parts = new ArrayIterator($parts);
        }
        Preconditions::checkArgument($parts instanceof Traversable, 'parts must be an array or a Traversable');
        $transformed = [];
        foreach ($parts as $part) {
            $value = call_user_func($this->converter, $part);
            if ($value !== null) {
                $transformed[] = ToStringHelper::valueToString($value);
            }
        }
        return implode($this->separator, $transformed);
    }
}
