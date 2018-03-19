<?php
declare(strict_types=1);

namespace precore\util;

use precore\lang\ClassCastException;
use Traversable;

/**
 * A comparator, with additional methods to support common operations.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Ordering implements Comparator
{
    private static $NATURAL;
    private static $TO_STRING;

    /**
     * @var Comparator
     */
    private $comparator;

    /**
     * Ordering constructor.
     * @param Comparator $comparator
     */
    private function __construct(Comparator $comparator)
    {
        $this->comparator = $comparator;
    }

    public static function init() : void
    {
        self::$NATURAL = Ordering::from(ComparableComparator::instance());
        self::$TO_STRING = Ordering::from(StringComparator::$BINARY)->onResultOf(Functions::toStringFunction());
    }

    /**
     * Construct an {@link Ordering} from the passed comparator.
     *
     * @param Comparator $comparator
     * @return Ordering
     */
    public static function from(Comparator $comparator) : Ordering
    {
        return new Ordering($comparator);
    }

    /**
     * Creates a {@link Ordering} based on natural ordering which means all sorted elements
     * have to implement {@link Comparable} interface and must be compatible with each other.
     *
     * @return Ordering
     */
    public static function natural() : Ordering
    {
        return self::$NATURAL;
    }

    /**
     * The created {@link Ordering} will compare the string representation
     * of all elements with {@link StringComparator::$BINARY}.
     *
     * @return Ordering
     */
    public static function usingToString() : Ordering
    {
        return self::$TO_STRING;
    }

    /**
     * Returns the reverse of this ordering.
     *
     * @return Ordering
     */
    public function reverse() : Ordering
    {
        return Ordering::from(Collections::reverseOrder($this));
    }

    /**
     * Returns an ordering that treats null as less than all other values and uses this to compare non-null values.
     *
     * @return Ordering
     */
    public function nullsFirst() : Ordering
    {
        return Ordering::from(Collections::comparatorFrom(
            function ($object1, $object2) {
                return $object1 === null
                    ? -1
                    : ($object2 === null
                        ? 1
                        : $this->compare($object1, $object2));
            }
        ));
    }

    /**
     * Returns an ordering that treats null as greater than all other values
     * and uses this ordering to compare non-null values.
     *
     * @return Ordering
     */
    public function nullsLast() : Ordering
    {
        return Ordering::from(Collections::comparatorFrom(
            function ($object1, $object2) {
                return $object1 === null
                    ? 1
                    : ($object2 === null
                        ? -1
                        : $this->compare($object1, $object2));
            }
        ));
    }

    /**
     * Returns a new ordering which orders elements by first applying a function to them,
     * then comparing those results using this.
     *
     * @param callable $function
     * @return Ordering
     */
    public function onResultOf(callable $function) : Ordering
    {
        return Ordering::from(Collections::comparatorFrom(
            function ($object1, $object2) use ($function) {
                return $this->compare(
                    Functions::call($function, $object1),
                    Functions::call($function, $object2)
                );
            }
        ));
    }

    /**
     * Returns an ordering which first uses the ordering this, but which in the event of a "tie",
     * then delegates to secondaryComparator.
     *
     * @param Comparator $secondaryComparator
     * @return Ordering
     */
    public function compound(Comparator $secondaryComparator) : Ordering
    {
        return Ordering::from(Collections::comparatorFrom(
            function ($object1, $object2) use ($secondaryComparator) {
                $res = $this->compare($object1, $object2);
                return $res !== 0 ? $res : $secondaryComparator->compare($object1, $object2);
            }
        ));
    }

    /**
     * Returns the least of the specified values according to this ordering.
     *
     * @param Traversable $traversable
     * @return mixed
     * @throws \OutOfBoundsException if $traversable is empty
     */
    public function min(Traversable $traversable)
    {
        $array = iterator_to_array($traversable, false);
        Arrays::sort($array, $this);
        return Preconditions::checkElementExists($array, 0);
    }

    /**
     * Returns the greatest of the specified values according to this ordering.
     *
     * @param Traversable $traversable
     * @return mixed
     * @throws \OutOfBoundsException if $traversable is empty
     */
    public function max(Traversable $traversable)
    {
        return $this->reverse()->min($traversable);
    }

    /**
     * @param $object1
     * @param $object2
     * @return int a negative integer, zero, or a positive integer
     *         as the first argument is less than, equal to, or greater than the second.
     * @throws ClassCastException - if the arguments' types prevent them from being compared by this comparator.
     */
    public function compare($object1, $object2) : int
    {
        return $this->comparator->compare($object1, $object2);
    }
}
Ordering::init();
