<?php

namespace precore\util;

use PHPUnit_Framework_TestCase;
use precore\util\concurrent\TimeUnit;

/**
 * Class RangeTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class RangeTest extends PHPUnit_Framework_TestCase
{
    private $input = ['a', 'b', 'c', 'd', 'e', 'f'];

    /**
     * @test
     */
    public function shouldCreateOpenRange()
    {
        $range = Range::open('b', 'e', Ordering::usingToString());
        self::assertSame(['c', 'd'], FluentIterable::of($this->input)->filter($range)->toArray());
    }

    /**
     * @test
     */
    public function shouldCreateClosedRange()
    {
        $range = Range::closed('b', 'e', Ordering::usingToString());
        self::assertSame(['b', 'c', 'd', 'e'], FluentIterable::of($this->input)->filter($range)->toArray());
    }

    /**
     * @test
     */
    public function shouldCreateOpenClosedRange()
    {
        $range = Range::openClosed('b', 'e', Ordering::usingToString());
        self::assertSame(['c', 'd', 'e'], FluentIterable::of($this->input)->filter($range)->toArray());
    }

    /**
     * @test
     */
    public function shouldCreateClosedOpenRange()
    {
        $range = Range::closedOpen('b', 'e', Ordering::usingToString());
        self::assertSame(['b', 'c', 'd'], FluentIterable::of($this->input)->filter($range)->toArray());
    }

    /**
     * @test
     */
    public function shouldCreateAllRange()
    {
        self::assertSame($this->input, FluentIterable::of($this->input)->filter(Range::all())->toArray());
    }

    /**
     * @test
     */
    public function shouldCreateGreaterThanRange()
    {
        $range = Range::greaterThan('b', Ordering::usingToString());
        self::assertSame(['c', 'd', 'e', 'f'], FluentIterable::of($this->input)->filter($range)->toArray());
    }

    /**
     * @test
     */
    public function shouldCreateAtLeastRange()
    {
        $range = Range::atLeast('b', Ordering::usingToString());
        self::assertSame(['b', 'c', 'd', 'e', 'f'], FluentIterable::of($this->input)->filter($range)->toArray());
    }

    /**
     * @test
     */
    public function shouldCreateLessThanRange()
    {
        $range = Range::lessThan('e', Ordering::usingToString());
        self::assertSame(['a', 'b', 'c', 'd'], FluentIterable::of($this->input)->filter($range)->toArray());
    }

    /**
     * @test
     */
    public function shouldCreateAtMostRange()
    {
        $range = Range::atMost('d', Ordering::usingToString());
        self::assertSame(['a', 'b', 'c', 'd'], FluentIterable::of($this->input)->filter($range)->toArray());
    }

    /**
     * @test
     */
    public function shouldUseNaturalOrdering()
    {
        $range = Range::closed(TimeUnit::$SECONDS, TimeUnit::$HOURS);
        $result = FluentIterable::of(TimeUnit::values())->filter($range);
        self::assertTrue(Iterables::contains($result, TimeUnit::$SECONDS));
        self::assertTrue(Iterables::contains($result, TimeUnit::$MINUTES));
        self::assertTrue(Iterables::contains($result, TimeUnit::$HOURS));
        self::assertFalse(Iterables::contains($result, TimeUnit::$MILLISECONDS));
        self::assertFalse(Iterables::contains($result, TimeUnit::$DAYS));
    }

    /**
     * @test
     */
    public function shouldCheckContains()
    {
        self::assertTrue(Range::closed('b', 'e', Ordering::usingToString())->contains('b'));
        self::assertTrue(Range::closed('b', 'e', Ordering::usingToString())->contains('e'));
        self::assertFalse(Range::closed('b', 'e', Ordering::usingToString())->contains('a'));
        self::assertFalse(Range::open('b', 'e', Ordering::usingToString())->contains('b'));
        self::assertTrue(Range::open('b', 'e', Ordering::usingToString())->contains('c'));
    }

    /**
     * @test
     */
    public function shouldUseNaturalStringOrdering()
    {
        self::assertFalse(Range::open('b', 'e')->contains('b'));
        self::assertTrue(Range::open('b', 'e')->contains('c'));
    }

    /**
     * @test
     */
    public function shouldUseNaturalNumericOrdering()
    {
        self::assertTrue(Range::closed(0, 5)->isConnected(Range::closed(3, 9)));
        self::assertFalse(Range::open(4.1, 5)->isConnected(Range::open(5, 10)));
    }

    /**
     * @test
     */
    public function shouldCheckContainsAll()
    {
        $bc = Iterators::forArray(['b', 'c']);
        self::assertTrue(Range::closed('b', 'e', Ordering::usingToString())->containsAll($bc));
        self::assertFalse(Range::open('b', 'e', Ordering::usingToString())->containsAll($bc));
        self::assertTrue(Range::closed('a', 'd', Ordering::usingToString())->containsAll($bc));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfLowerIsHigherThanUpper()
    {
        Range::closed('b', 'a', Ordering::usingToString());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfLowerIsEqualToUpper()
    {
        Range::open('a', 'a', Ordering::usingToString());
    }

    /**
     * @test
     */
    public function shouldBeEmpty()
    {
        self::assertTrue(Range::openClosed('a', 'a', Ordering::usingToString())->isEmpty());
        self::assertTrue(Range::closedOpen('a', 'a', Ordering::usingToString())->isEmpty());
    }

    /**
     * @test
     */
    public function shouldEnclose()
    {
        $comparator = Numbers::naturalOrdering();
        self::assertTrue(Range::closed(3, 6, $comparator)->encloses(Range::closed(4, 5, $comparator)));
        self::assertTrue(Range::open(3, 6, $comparator)->encloses(Range::open(3, 6, $comparator)));
        self::assertTrue(Range::closed(3, 6, $comparator)->encloses(Range::closedOpen(4, 4, $comparator)));
        self::assertFalse(Range::openClosed(3, 6, $comparator)->encloses(Range::closed(3, 6, $comparator)));
        self::assertFalse(Range::closed(4, 5, $comparator)->encloses(Range::open(3, 6, $comparator)));
        self::assertFalse(Range::closed(3, 6, $comparator)->encloses(Range::openClosed(1, 1, $comparator)));
    }

    /**
     * @test
     */
    public function shouldBeConnected()
    {
        $comparator = Numbers::naturalOrdering();
        self::assertTrue(Range::closed(3, 5, $comparator)->isConnected(Range::open(5, 10, $comparator)));
        self::assertTrue(Range::closed(0, 9, $comparator)->isConnected(Range::closed(3, 4, $comparator)));
        self::assertTrue(Range::closed(0, 5, $comparator)->isConnected(Range::closed(3, 9, $comparator)));
        self::assertFalse(Range::open(3, 5, $comparator)->isConnected(Range::open(5, 10, $comparator)));
        self::assertFalse(Range::closed(1, 5, $comparator)->isConnected(Range::closed(6, 10, $comparator)));
    }

    /**
     * @test
     */
    public function shouldCreateIntersection()
    {
        $cmp = Numbers::naturalOrdering();
        $res1 = Range::openClosed(5, 5, $cmp);
        self::assertTrue(Range::closed(3, 5, $cmp)->intersection(Range::open(5, 10, $cmp))->equals($res1));

        $res2 = Range::closed(3, 4, $cmp);
        self::assertTrue(Range::closed(0, 9, $cmp)->intersection(Range::closed(3, 4, $cmp))->equals($res2));

        $res3 = Range::closed(3, 5, $cmp);
        self::assertTrue(Range::closed(0, 5, $cmp)->intersection(Range::closed(3, 9, $cmp))->equals($res3));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfNoIntersection1()
    {
        $cmp = Numbers::naturalOrdering();
        Range::open(3, 5, $cmp)->intersection(Range::open(5, 10, $cmp));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfNoIntersection2()
    {
        $cmp = Numbers::naturalOrdering();
        Range::closed(1, 5, $cmp)->intersection(Range::closed(6, 10, $cmp));
    }

    /**
     * @test
     */
    public function shouldSpan()
    {
        $cmp = Numbers::naturalOrdering();
        $res1 = Range::closedOpen(3, 10, $cmp);
        self::assertTrue(Range::closed(3, 5, $cmp)->span(Range::open(5, 10, $cmp))->equals($res1));

        $res2 = Range::closed(0, 9, $cmp);
        self::assertTrue(Range::closed(0, 9, $cmp)->span(Range::closed(3, 4, $cmp))->equals($res2));

        $res3 = Range::closed(0, 9, $cmp);
        self::assertTrue(Range::closed(0, 5, $cmp)->span(Range::closed(3, 9, $cmp))->equals($res3));

        $res4 = Range::open(3, 10, $cmp);
        self::assertTrue(Range::open(3, 5, $cmp)->span(Range::open(5, 10, $cmp))->equals($res4));

        $res5 = Range::closed(1, 10, $cmp);
        self::assertTrue(Range::closed(1, 5, $cmp)->span(Range::closed(6, 10, $cmp))->equals($res5));
    }
}
