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
use BadMethodCallException;
use precore\lang\Object;
use precore\lang\ObjectInterface;
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
abstract class Joiner extends Object
{
    private $separator;

    /**
     * @param $separator
     */
    protected function __construct($separator)
    {
        Preconditions::checkArgument(is_string($separator), 'separator must be a string');
        $this->separator = $separator;
    }

    /**
     * Returns a joiner which automatically places {@link Joiner::separator} between consecutive elements.
     *
     * @param string $separator
     * @return Joiner
     */
    public static function on($separator)
    {
        return new SimpleJoiner($separator);
    }

    /**
     * Returns a joiner with the same behavior as this joiner, except automatically skipping over any
     * provided null elements.
     *
     * @return Joiner
     */
    public function skipNulls()
    {
        return new SkipNullJoiner($this->separator);
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
        return new UseForNullJoiner($this->separator, $nullText);
    }

    /**
     * @param FluentIterable $iterable
     * @return FluentIterable
     */
    protected abstract function modifyIterable(FluentIterable $iterable);

    /**
     * Returns a string containing the string representation of each element of $parts, using the previously
     * configured separator between each.
     *
     * @param array|Traversable $parts
     * @return string
     */
    final public function join($parts)
    {
        if (is_array($parts)) {
            $parts = new ArrayIterator($parts);
        }
        Preconditions::checkArgument($parts instanceof Traversable, 'parts must be an array or a Traversable');
        $iterator = $this->modifyIterable(FluentIterable::from($parts))
            ->transform(
                function ($element) {
                    return ToStringHelper::valueToString($element);
                }
            )
            ->iterator();
        $res = '';
        while ($iterator->valid()) {
            $res .= $iterator->current();
            $iterator->next();
            if ($iterator->valid()) {
                $res .= $this->separator;
            }
        }
        return $res;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('separator', $this->separator)
            ->toString();
    }

    public function equals(ObjectInterface $object = null)
    {
        /* @var $object Joiner */
        return $object !== null
            && $this->getClassName() === $object->getClassName()
            && $this->separator === $object->separator;
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class SimpleJoiner extends Joiner
{
    /**
     * @param FluentIterable $iterable
     * @return FluentIterable
     */
    protected function modifyIterable(FluentIterable $iterable)
    {
        return $iterable->filter(
            function ($element) {
                Preconditions::checkNotNull(
                    $element,
                    "There is a null input, consider to use skipNulls() or useForNull()"
                );
                return true;
            }
        );
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class SkipNullJoiner extends Joiner
{
    public function skipNulls()
    {
        throw new BadMethodCallException('already specified skipNulls');
    }

    public function useForNull($nullText)
    {
        throw new BadMethodCallException('already specified skipNulls');
    }

    /**
     * @param FluentIterable $iterable
     * @return FluentIterable
     */
    protected function modifyIterable(FluentIterable $iterable)
    {
        return $iterable->filter(Predicates::notNull());
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class UseForNullJoiner extends Joiner
{
    private $useForNull;

    public function __construct($separator, $nullText)
    {
        parent::__construct($separator);
        Preconditions::checkArgument(is_string($nullText), 'nullText must be a string');
        $this->useForNull = $nullText;
    }

    public function skipNulls()
    {
        throw new BadMethodCallException('already specified useForNull');
    }

    public function useForNull($nullText)
    {
        throw new BadMethodCallException('already specified useForNull');
    }

    /**
     * @param FluentIterable $iterable
     * @return FluentIterable
     */
    protected function modifyIterable(FluentIterable $iterable)
    {
        return $iterable->transform(
            function ($element) {
                return $element !== null ? $element : $this->useForNull;
            }
        );
    }

    public function equals(ObjectInterface $object = null)
    {
        /* @var $object UseForNullJoiner */
        return parent::equals($object) && $this->useForNull === $object->useForNull;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)
            ->add('parent', parent::toString())
            ->add('useForNull', $this->useForNull)
            ->toString();
    }
}
