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

use Closure;
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
    public static function start()
    {
        if (self::$active === null) {
            self::$active = new ActiveComparisonChain(
                new InactiveComparisonChain(-1),
                new InactiveComparisonChain(1)
            );
        }
        return self::$active;
    }

    protected function __construct()
    {
    }

    /**
     * @param $left
     * @param $right
     * @param Comparator $comparator
     * @return ComparisonChain
     */
    abstract public function withComparator($left, $right, Comparator $comparator);

    /**
     * @param $left
     * @param $right
     * @param Closure $closure
     * @return ComparisonChain
     */
    abstract public function withClosure($left, $right, Closure $closure);

    /**
     * @param Comparable $left
     * @param Comparable $right
     * @return ComparisonChain
     */
    abstract public function compare(Comparable $left, Comparable $right);

    /**
     * @return int
     */
    abstract public function result();
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

    protected function __construct(ComparisonChain $less, ComparisonChain $greater)
    {
        $this->less = $less;
        $this->greater = $greater;
    }

    /**
     * @param $left
     * @param $right
     * @param Comparator $comparator
     * @return ComparisonChain
     */
    public function withComparator($left, $right, Comparator $comparator)
    {
        return $this->classify($comparator->compare($left, $right));
    }

    /**
     * @param $left
     * @param $right
     * @param Closure $closure
     * @return ComparisonChain
     */
    public function withClosure($left, $right, Closure $closure)
    {
        return $this->classify(call_user_func($closure, $left, $right));
    }

    /**
     * @param Comparable $left
     * @param Comparable $right
     * @return ComparisonChain
     */
    public function compare(Comparable $left, Comparable $right)
    {
        return $this->classify($left->compareTo($right));
    }

    /**
     * @return int
     */
    public function result()
    {
        return 0;
    }

    /**
     * @param int $result
     * @return ComparisonChain
     */
    private function classify($result)
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

    protected function __construct($result)
    {
        $this->result = (int) $result;
    }

    /**
     * @param $left
     * @param $right
     * @param Comparator $comparator
     * @return ComparisonChain
     */
    public function withComparator($left, $right, Comparator $comparator)
    {
        return $this;
    }

    /**
     * @param $left
     * @param $right
     * @param Closure $closure
     * @return ComparisonChain
     */
    public function withClosure($left, $right, Closure $closure)
    {
        return $this;
    }

    /**
     * @param Comparable $left
     * @param Comparable $right
     * @return ComparisonChain
     */
    public function compare(Comparable $left, Comparable $right)
    {
        return $this;
    }

    /**
     * @return int
     */
    public function result()
    {
        return $this->result;
    }
}
