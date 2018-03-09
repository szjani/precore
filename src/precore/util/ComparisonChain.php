<?php
declare(strict_types=1);

namespace precore\util;

use Closure;
use precore\lang\ClassCastException;
use precore\lang\Comparable;

/**
 * A utility for performing a "lazy" chained comparison statement, which
 * performs comparisons only until it finds a nonzero result.
 *
 * Based on ComparisonChain found in Google's Guava library.
 *
 * @package precore\util
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class ComparisonChain
{
    private static $active;

    /**
     * @return ComparisonChain
     */
    public static function start() : ComparisonChain
    {
        if (self::$active === null) {
            self::$active = new ActiveComparisonChain(
                new InactiveComparisonChain(-1),
                new InactiveComparisonChain(1)
            );
        }
        return self::$active;
    }

    /**
     * @param $left
     * @param $right
     * @param Comparator $comparator
     * @return ComparisonChain
     */
    abstract public function withComparator($left, $right, Comparator $comparator) : ComparisonChain;

    /**
     * @param $left
     * @param $right
     * @param Closure $closure
     * @return ComparisonChain
     */
    abstract public function withClosure($left, $right, Closure $closure) : ComparisonChain;

    /**
     * @param Comparable $left
     * @param Comparable $right
     * @return ComparisonChain
     */
    abstract public function compare(Comparable $left, Comparable $right) : ComparisonChain;

    /**
     * @return int
     */
    abstract public function result() : int;
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ActiveComparisonChain extends ComparisonChain
{
    private $less;
    private $greater;

    public function __construct(ComparisonChain $less, ComparisonChain $greater)
    {
        $this->less = $less;
        $this->greater = $greater;
    }

    /**
     * @param $left
     * @param $right
     * @param Comparator $comparator
     * @return ComparisonChain
     * @throws ClassCastException if the arguments' types prevent them from being compared by this comparator
     */
    public function withComparator($left, $right, Comparator $comparator) : ComparisonChain
    {
        return $this->classify($comparator->compare($left, $right));
    }

    /**
     * @param $left
     * @param $right
     * @param Closure $closure
     * @return ComparisonChain
     */
    public function withClosure($left, $right, Closure $closure) : ComparisonChain
    {
        return $this->classify(Functions::call($closure, $left, $right));
    }

    /**
     * @param Comparable $left
     * @param Comparable $right
     * @return ComparisonChain
     */
    public function compare(Comparable $left, Comparable $right) : ComparisonChain
    {
        return $this->classify($left->compareTo($right));
    }

    /**
     * @return int
     */
    public function result() : int
    {
        return 0;
    }

    /**
     * @param int $result
     * @return ComparisonChain
     */
    private function classify($result) : ComparisonChain
    {
        return $result < 0 ? $this->less : ($result > 0 ? $this->greater : $this);
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class InactiveComparisonChain extends ComparisonChain
{
    private $result;

    public function __construct($result)
    {
        $this->result = (int) $result;
    }

    /**
     * @param $left
     * @param $right
     * @param Comparator $comparator
     * @return ComparisonChain
     */
    public function withComparator($left, $right, Comparator $comparator) : ComparisonChain
    {
        return $this;
    }

    /**
     * @param $left
     * @param $right
     * @param Closure $closure
     * @return ComparisonChain
     */
    public function withClosure($left, $right, Closure $closure) : ComparisonChain
    {
        return $this;
    }

    /**
     * @param Comparable $left
     * @param Comparable $right
     * @return ComparisonChain
     */
    public function compare(Comparable $left, Comparable $right) : ComparisonChain
    {
        return $this;
    }

    /**
     * @return int
     */
    public function result() : int
    {
        return $this->result;
    }
}
