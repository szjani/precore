<?php
declare(strict_types=1);

namespace precore\util;

use ArrayObject;
use EmptyIterator;
use precore\lang\ClassCastException;
use SplHeap;

/**
 * This class consists exclusively of static methods that operate on or return collections.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class Collections
{
    /**
     * @var Joiner
     */
    private static $STANDARD_JOINER;

    private function __construct()
    {
    }

    public static function init() : void
    {
        self::$STANDARD_JOINER = Joiner::on(', ')->useForNull('null');
    }

    /**
     * @return Joiner
     */
    public static function standardJoiner() : Joiner
    {
        return self::$STANDARD_JOINER;
    }

    /**
     * Creates a comparator function based on the given {@link Comparator} thus it can be passed
     * to any native sorting function like {@link usort}.
     *
     * @param Comparator $comparator
     * @return callable
     */
    public static function compareFunctionFor(Comparator $comparator) : callable
    {
        return function ($object1, $object2) use ($comparator) {
            return $comparator->compare($object1, $object2);
        };
    }

    /**
     * Creates a {@link Comparator} from the given function.
     * The function will get two parameters are have to return a negative, 0, or positive
     * number depending on how the two parameters are compared to each other.
     *
     * @param callable $comparator
     * @return Comparator
     */
    public static function comparatorFrom(callable $comparator) : Comparator
    {
        return new CallableBasedComparator($comparator);
    }

    /**
     * Sorts the specified object according to the order induced by the specified comparator.
     * All elements in the object must be mutually comparable using the specified comparator
     * (that is, c.compare(e1, e2) must not throw a ClassCastException for any elements e1 and e2 in the object).
     *
     * <p>
     * If the specified comparator is null, this method sorts the specified {@link ArrayObject} into ascending order,
     * according to the natural ordering of its elements. All elements in the object must implement
     * the {@link Comparable} interface. Furthermore, all elements in the object must be mutually comparable
     * (that is, e1.compareTo(e2) must not throw a ClassCastException for any elements e1 and e2 in the object).
     *
     * @param ArrayObject $arrayObject
     * @param Comparator $comparator
     */
    public static function sort(ArrayObject $arrayObject, Comparator $comparator = null) : void
    {
        if ($comparator === null) {
            $comparator = ComparableComparator::instance();
        }
        $arrayObject->uasort(self::compareFunctionFor($comparator));
    }

    /**
     * Returns a comparator that imposes the reverse ordering of the specified comparator.
     *
     * <p>
     * If the specified comparator is null, it returns a comparator that imposes the reverse of the natural ordering
     * on a collection of objects that implement the {@link Comparable} interface.
     * (The natural ordering is the ordering imposed by the objects' own compareTo method.)
     *
     * <p>
     * This enables a simple idiom for sorting (or maintaining) collections (or arrays) of objects
     * that implement the {@link Comparable} interface in reverse-natural-order.
     * For example, suppose a is an array. Then:
     *
     * <pre>
     *   Arrays::sortWith(a, Collections::reverseOrder());
     * </pre>
     *
     * sorts the array in reverse order.
     *
     * @param Comparator $comparator
     * @return Comparator
     */
    public static function reverseOrder(Comparator $comparator = null) : Comparator
    {
        if ($comparator === null) {
            $comparator = ComparableComparator::instance();
        }
        return new ReverseComparator($comparator);
    }

    /**
     * Creates an {@link SplHeap} based on the given comparator object.
     * All elements in the heap must be mutually comparable using the specified comparator
     * (that is, c.compare(e1, e2) must not throw a ClassCastException for any elements e1 and e2 in the heap).
     *
     * <p>
     * If the specified comparator is null, all elements in the heap must implement
     * the {@link Comparable} interface. Furthermore, all elements in the heap must be mutually comparable
     * (that is, e1.compareTo(e2) must not throw a ClassCastException for any elements e1 and e2 in the heap).
     *
     * @param Comparator $comparator
     * @return SplHeap
     */
    public static function createHeap(Comparator $comparator = null) : SplHeap
    {
        if ($comparator === null) {
            $comparator = ComparableComparator::instance();
        }
        return new ComparatorBasedHeap($comparator);
    }

    /**
     * @return \Iterator
     */
    public static function emptyIterator() : \Iterator
    {
        return new EmptyIterator();
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ComparatorBasedHeap extends SplHeap
{
    /**
     * @var Comparator
     */
    private $comparator;

    /**
     * @param Comparator $comparator
     */
    public function __construct(Comparator $comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * (PHP 5 &gt;= 5.3.0)<br/>
     * Compare elements in order to place them correctly in the heap while sifting up.
     * @link http://php.net/manual/en/splheap.compare.php
     * @param mixed $value1 <p>
     * The value of the first node being compared.
     * </p>
     * @param mixed $value2 <p>
     * The value of the second node being compared.
     * </p>
     * @return int Result of the comparison, positive integer if <i>value1</i> is greater than <i>value2</i>, 0 if they are equal, negative integer otherwise.
     * </p>
     * <p>
     * Having multiple elements with the same value in a Heap is not recommended. They will end up in an arbitrary relative position.
     */
    protected function compare($value1, $value2) : int
    {
        return $this->comparator->compare($value1, $value2);
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ReverseComparator implements Comparator
{
    /**
     * @var Comparator
     */
    private $comparator;

    /**
     * @param Comparator $comparator
     */
    public function __construct(Comparator $comparator)
    {
        $this->comparator = $comparator;
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
        return $this->comparator->compare($object2, $object1);
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class CallableBasedComparator implements Comparator
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * CallableBasedComparator constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
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
        return Functions::call($this->callable, $object1, $object2);
    }
}
Collections::init();
