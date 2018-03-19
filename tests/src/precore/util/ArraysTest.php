<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;
use precore\lang\Color;

/**
 * @package precore\util
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ArraysTest extends TestCase
{
    public function testSort()
    {
        $list = [Color::$BLUE, Color::$RED];
        Arrays::sort($list);
        self::assertSame(Color::$RED, $list[0]);
        self::assertSame(Color::$BLUE, $list[1]);
    }

    /**
     * @test
     */
    public function shouldSortWith()
    {
        $list = ['b', 'a'];
        Arrays::sort($list, StringComparator::$BINARY);
        self::assertSame('a', $list[0]);
        self::assertSame('b', $list[1]);
    }
}
