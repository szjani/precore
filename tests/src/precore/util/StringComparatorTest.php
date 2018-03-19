<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;

/**
 * Class NativeComparatorTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class StringComparatorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCompareString()
    {
        self::assertLessThan(0, StringComparator::$BINARY->compare('a', 'b'));
        self::assertGreaterThan(0, StringComparator::$BINARY->compare('a', 'A'));
        self::assertGreaterThan(0, StringComparator::$BINARY->compare('2', '10'));
    }

    /**
     * @test
     */
    public function shouldCompareCaseInsensitiveString()
    {
        self::assertEquals(0, StringComparator::$BINARY_CASE_INSENSITIVE->compare('a', 'A'));
    }

    /**
     * @test
     */
    public function shouldUseNaturalOrdering()
    {
        self::assertLessThan(0, StringComparator::$NATURAL->compare('2', '10'));
        self::assertGreaterThan(0, StringComparator::$NATURAL->compare('a2', 'A10'));
    }

    /**
     * @test
     */
    public function shouldUseCaseInsensitiveNaturalOrdering()
    {
        self::assertLessThan(0, StringComparator::$NATURAL_CASE_INSENSITIVE->compare('a2', 'A10'));
    }
}
