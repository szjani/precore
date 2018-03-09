<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;

/**
 * Class NumbersTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class NumbersTest extends TestCase
{
    /**
     * @test
     */
    public function shouldHandleFloats()
    {
        $array = [1.1, 1.2];
        Arrays::sort($array, Numbers::naturalOrdering());
        self::assertSame([1.1, 1.2], $array);

        $array = [1.2, 1.1];
        Arrays::sort($array, Numbers::naturalOrdering());
        self::assertSame([1.1, 1.2], $array);
    }
}
