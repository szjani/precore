<?php
declare(strict_types=1);

namespace precore\util;

use ArrayObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectionsTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class CollectionsTest extends TestCase
{
    /**
     * @test
     */
    public function shouldUseBinaryStringComparison()
    {
        $heap = Collections::createHeap(StringComparator::$BINARY_CASE_INSENSITIVE);
        $heap->insert('a');
        $heap->insert('B');
        self::assertEquals('B', $heap->extract());
        self::assertEquals('a', $heap->extract());
    }

    /**
     * @test
     */
    public function shouldCompareReverseOrder()
    {
        $comparator = Collections::reverseOrder(StringComparator::$BINARY);
        self::assertGreaterThan(0, $comparator->compare('a', 'b'));
    }

    /**
     * @test
     */
    public function shouldCompareComparable()
    {
        $heap = Collections::createHeap();
        $heap->insert(NumberFixture::$ONE);
        $heap->insert(NumberFixture::$TWO);
        self::assertSame(NumberFixture::$TWO, $heap->extract());
        self::assertSame(NumberFixture::$ONE, $heap->extract());
    }

    /**
     * @test
     */
    public function shouldCompareWithReverseNaturalOrder()
    {
        self::assertGreaterThan(0, Collections::reverseOrder()->compare(NumberFixture::$ONE, NumberFixture::$TWO));
    }

    /**
     * @test
     */
    public function shouldSortArrayObject()
    {
        $obj = new ArrayObject([NumberFixture::$TWO, NumberFixture::$ONE]);
        Collections::sort($obj);
        $iterator = $obj->getIterator();
        self::assertSame(NumberFixture::$ONE, $iterator->current());
        $iterator->next();
        self::assertSame(NumberFixture::$TWO, $iterator->current());
    }

    /**
     * @test
     */
    public function shouldSortArrayObjectWithComparator()
    {
        $obj = new ArrayObject(['b', 'a']);
        Collections::sort($obj, StringComparator::$BINARY);
        $iterator = $obj->getIterator();
        self::assertSame('a', $iterator->current());
        $iterator->next();
        self::assertSame('b', $iterator->current());
    }
}
