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
    private $separator;
    private $skipNulls;
    private $useForNull;

    /**
     * @param $separator
     * @param $skipNulls
     * @param $useForNull
     */
    private function __construct($separator, $skipNulls, $useForNull)
    {
        Preconditions::checkArgument(is_string($separator), 'separator must be a string');
        $this->separator = $separator;
        $this->skipNulls = $skipNulls;
        $this->useForNull = $useForNull;
    }

    /**
     * Returns a joiner which automatically places {@link Joiner::separator} between consecutive elements.
     *
     * @param string $separator
     * @return Joiner
     */
    public static function on($separator)
    {
        return new Joiner($separator, false, null);
    }

    /**
     * Returns a joiner with the same behavior as this joiner, except automatically skipping over any
     * provided null elements.
     *
     * @return Joiner
     */
    public function skipNulls()
    {
        return new Joiner($this->separator, true, $this->useForNull);
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
        return new Joiner($this->separator, $this->skipNulls, $nullText);
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

        $result = FluentIterable::from($parts)
            ->transform(
                function ($element) {
                    return $element !== null ? $element : $this->useForNull;
                }
            )
            ->filter(
                function ($element) {
                    if (!$this->skipNulls) {
                        Preconditions::checkNotNull($element, "There is a null input, consider to use skipNulls()");
                    }
                    return $element !== null;
                }
            )
            ->transform(
                function ($element) {
                    return ToStringHelper::valueToString($element);
                }
            )
            ->toArray();
        return implode($this->separator, $result);
    }
}
