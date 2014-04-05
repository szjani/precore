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

use Closure;
use precore\lang\Comparable;

class ActiveComparisonChain extends ComparisonChain
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
     * @param callable $closure
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
