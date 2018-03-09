<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;

class ComparisonChainTest extends TestCase
{
    public function testWithClosures()
    {
        $strcmp = function ($left, $right) {
            return strcmp($left, $right);
        };
        $result = ComparisonChain::start()
            ->withClosure('aaa', 'aaa', $strcmp)
            ->withClosure('abc', 'bcd', $strcmp)
            ->withClosure('abc', 'bcd', $strcmp)
            ->result();
        self::assertTrue($result < 0);

        $result = ComparisonChain::start()
            ->withClosure('abc', 'abc', $strcmp)
            ->withClosure('bcd', 'abc', $strcmp)
            ->result();
        self::assertTrue(0 < $result);
    }

    /**
     * @test
     */
    public function expected0()
    {
        $strcmp = function ($left, $right) {
            return strcmp($left, $right);
        };
        $result = ComparisonChain::start()
            ->withClosure('aaa', 'aaa', $strcmp)
            ->result();
        self::assertTrue($result == 0);
    }

    public function testLazyWithComparator()
    {
        $comparator = $this->getMockBuilder('\precore\util\Comparator')->getMock();
        $comparator
            ->expects(self::once())
            ->method('compare')
            ->will(self::returnValue(-1));
        $result = ComparisonChain::start()
            ->withComparator('abc', 'bcd', $comparator)
            ->withComparator('aaa', 'bbb', $comparator)
            ->result();
        self::assertTrue($result < 0);
    }
}
