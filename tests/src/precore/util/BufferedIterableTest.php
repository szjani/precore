<?php
declare(strict_types=1);

namespace precore\util;

use ArrayIterator;
use PHPUnit\Framework\TestCase;

/**
 * Class BufferedIterableTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class BufferedIterableTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeEmpty()
    {
        $bufferedIterable = BufferedIterable::withChunkFunction(
            function () {
                return Optional::absent();
            }
        );
        self::assertTrue(Iterables::isEmpty($bufferedIterable));
    }

    /**
     * @test
     */
    public function shouldBeCalledUntilChunkProviderGivesEmptyResult()
    {
        $offsets = [];
        $bufferedIterable = BufferedIterable::withChunkFunction(
            function ($offset) use (&$offsets) {
                $offsets[] = $offset;
                if ($offset == 0) {
                    return Optional::of(new ArrayIterator([1, 2]));
                } elseif ($offset == 2) {
                    return Optional::of(new ArrayIterator([3, 4]));
                }  else {
                    return Optional::absent();
                }
            }
        )->providerCallLimit(PHP_INT_MAX);
        $result = FluentIterable::from($bufferedIterable)->toArray();
        self::assertEquals([1, 2, 3, 4], $result);
        self::assertEquals([0, 2, 4], $offsets);
    }

    /**
     * @test
     */
    public function shouldFilterResult()
    {
        $i = 0;
        $bufferedIterable = BufferedIterable::withChunkFunction(
            function () use (&$i) {
                if (5 < $i) {
                    return Optional::absent();
                }
                $res = new ArrayIterator([$i, $i + 1]);
                $i += 2;
                return Optional::of($res);
            }
        )->filter(
            function ($number) {
                return $number % 2 == 0;
            }
        )->providerCallLimit(PHP_INT_MAX);
        $result = FluentIterable::from($bufferedIterable)->toArray();
        self::assertEquals([0, 2, 4], $result);
    }

    /**
     * @test
     */
    public function shouldLimitResult()
    {
        $i = 0;
        $called = 0;
        $bufferedIterable = BufferedIterable::withChunkFunction(
            function () use (&$i, &$called) {
                $called++;
                if (5 < $i) {
                    return Optional::absent();
                }
                $res = new ArrayIterator([$i, $i + 1]);
                $i += 2;
                return Optional::of($res);
            }
        )
            ->providerCallLimit(PHP_INT_MAX)
            ->limit(3);
        $result = FluentIterable::from($bufferedIterable)->toArray();
        self::assertEquals([0, 1, 2], $result);
        self::assertEquals(2, $called);

        $i = 0;
        $result = FluentIterable::from($bufferedIterable)->toArray();
        self::assertEquals([0, 1, 2], $result);
    }

    /**
     * @test
     */
    public function shouldLimitProviderCalls()
    {
        $bufferedIterable = BufferedIterable::withChunkFunction(
            function () {
                return Optional::of(new ArrayIterator([1, 2]));
            }
        )->providerCallLimit(2);
        $result = FluentIterable::from($bufferedIterable)->toArray();
        self::assertEquals([1, 2, 1, 2], $result);
    }
}
