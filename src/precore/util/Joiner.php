<?php
declare(strict_types=1);

namespace precore\util;

use ArrayIterator;
use BadMethodCallException;
use precore\lang\BaseObject;
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
abstract class Joiner extends BaseObject
{
    private $separator;

    /**
     * @param string $separator
     */
    protected function __construct(string $separator)
    {
        $this->separator = $separator;
    }

    /**
     * Returns a joiner which automatically places {@link Joiner::separator} between consecutive elements.
     *
     * @param string $separator
     * @return Joiner
     * @throws \InvalidArgumentException if separator is not a string
     */
    public static function on($separator) : Joiner
    {
        return new SimpleJoiner($separator);
    }

    /**
     * Returns a joiner with the same behavior as this joiner, except automatically skipping over any
     * provided null elements.
     *
     * @return Joiner
     */
    public function skipNulls() : Joiner
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
    public function useForNull(string $nullText) : Joiner
    {
        return new UseForNullJoiner($this->separator, $nullText);
    }

    /**
     * @param FluentIterable $iterable
     * @return FluentIterable
     */
    protected abstract function modifyIterable(FluentIterable $iterable) : FluentIterable;

    /**
     * Returns a string containing the string representation of each element of $parts, using the previously
     * configured separator between each.
     *
     * @param array|Traversable $parts
     * @return string
     * @throws \InvalidArgumentException if $parts is not an array or a Traversable
     */
    final public function join($parts) : string
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

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('separator', $this->separator)
            ->toString();
    }

    public function equals(ObjectInterface $object = null) : bool
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
    protected function modifyIterable(FluentIterable $iterable) : FluentIterable
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
    public function skipNulls() : Joiner
    {
        throw new BadMethodCallException('already specified skipNulls');
    }

    public function useForNull(string $nullText) : Joiner
    {
        throw new BadMethodCallException('already specified skipNulls');
    }

    /**
     * @param FluentIterable $iterable
     * @return FluentIterable
     */
    protected function modifyIterable(FluentIterable $iterable) : FluentIterable
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

    public function __construct(string $separator, string $nullText)
    {
        parent::__construct($separator);
        $this->useForNull = $nullText;
    }

    public function skipNulls() : Joiner
    {
        throw new BadMethodCallException('already specified useForNull');
    }

    public function useForNull(string $nullText) : Joiner
    {
        throw new BadMethodCallException('already specified useForNull');
    }

    /**
     * @param FluentIterable $iterable
     * @return FluentIterable
     */
    protected function modifyIterable(FluentIterable $iterable) : FluentIterable
    {
        return $iterable->transform(
            function ($element) {
                return $element !== null ? $element : $this->useForNull;
            }
        );
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        /* @var $object UseForNullJoiner */
        return parent::equals($object) && $this->useForNull === $object->useForNull;
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('parent', parent::toString())
            ->add('useForNull', $this->useForNull)
            ->toString();
    }
}
