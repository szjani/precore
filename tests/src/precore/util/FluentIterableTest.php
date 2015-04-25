<?php
/*
 * Copyright (c) 2012-2015 Janos Szurovecz
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

use PHPUnit_Framework_TestCase;

/**
 * Class FluentIterableTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class FluentIterableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldFilterItems()
    {
        $fluentIterable = FluentIterable::of([1, null, 2, 3])->filter(Predicates::notNull());
        self::assertEquals([1, 2, 3], $fluentIterable->toArray());
        self::assertEquals([1, 2, 3], $fluentIterable->toArray());
    }

    /**
     * @test
     */
    public function shouldFilterType()
    {
        $uuid = UUID::randomUUID();
        $fluentIterable = FluentIterable::of([1, null, $uuid, 3])->filterBy(UUID::className());
        self::assertEquals([$uuid], $fluentIterable->toArray());
    }

    /**
     * @test
     */
    public function shouldLimitItems()
    {
        $result = FluentIterable::of([1, 2, 3])->limit(2)->toArray();
        self::assertEquals([1, 2], $result);
    }

    /**
     * @test
     */
    public function shouldTransform()
    {
        $double = function ($number) {
            return 2 * $number;
        };
        $result = FluentIterable::of([1, 2, 3])->transform($double)->toArray();
        self::assertEquals([2, 4, 6], $result);
    }

    /**
     * @test
     */
    public function shouldFilterAfterTransform()
    {
        $double = function ($number) {
            return 2 * $number;
        };
        $smallerThanFive = function ($number) {
            return $number < 5;
        };
        $result = FluentIterable::of([1, 2, 3])->transform($double)->filter($smallerThanFive)->toArray();
        self::assertEquals([2, 4], $result);
    }

    /**
     * @test
     */
    public function shouldRemoveZerosAndNulls()
    {
        $result = FluentIterable::of([1, 0, 3, null, 3, 0, 4])
            ->filter(
                Predicates::ands(
                    Predicates::notNull(),
                    Predicates::not(Predicates::equalTo(0))
                )
            )
            ->toArray();
        self::assertEquals([1, 3, 3, 4], $result);
    }

    /**
     * @test
     */
    public function shouldSkipItems()
    {
        $fluentIterable = FluentIterable::of([1, 2, 3]);
        self::assertEquals([2, 3], $fluentIterable->skip(1)->toArray());
        self::assertEquals([], $fluentIterable->skip(10)->toArray());
    }

    /**
     * @test
     */
    public function shouldJoinWithJoiner()
    {
        $result = FluentIterable::of([1, null, 2, 3])->join(Joiner::on(', ')->useForNull('null'));
        self::assertEquals('1, null, 2, 3', $result);
    }

    /**
     * @test
     */
    public function shouldReturnIndex()
    {
        $iterable = FluentIterable::of([1, 2]);
        self::assertEquals(1, $iterable->get(0));
        self::assertEquals(2, $iterable->get(1));
    }

    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function shouldThrowExceptionIfIndexIsInvalid()
    {
        FluentIterable::of([1, 2])->get(2);
    }

    /**
     * @test
     */
    public function shouldReturnToString()
    {
        self::assertEquals('[1, 2]', (string) FluentIterable::of([1, 2]));
    }

    /**
     * @test
     */
    public function shouldContainExistingAndNotContainNonExistingElement()
    {
        $iterable = FluentIterable::of([1, 2]);
        self::assertTrue($iterable->contains(1));
        self::assertFalse($iterable->contains(3));
    }

    /**
     * @test
     */
    public function shouldIsEmptyWork()
    {
        self::assertFalse(FluentIterable::of([1, 2])->isEmpty());
        self::assertTrue(FluentIterable::of([null])->filter(Predicates::notNull())->isEmpty());
    }

    /**
     * @test
     */
    public function shouldAnyMatch()
    {
        $evenPredicate = function ($number) {
            return $number % 2 === 0;
        };
        self::assertTrue(FluentIterable::of([1, 2])->anyMatch($evenPredicate));
        self::assertFalse(FluentIterable::of([1, 3])->anyMatch($evenPredicate));
    }

    /**
     * @test
     */
    public function shouldAllMatch()
    {
        $evenPredicate = function ($number) {
            return $number % 2 === 0;
        };
        self::assertTrue(FluentIterable::of([2, 4])->allMatch($evenPredicate));
        self::assertFalse(FluentIterable::of([2, 4, 1, 6])->allMatch($evenPredicate));
    }

    /**
     * @test
     */
    public function shouldReturnSize()
    {
        self::assertEquals(0, FluentIterable::of([])->size());
        self::assertEquals(1, FluentIterable::of([2])->size());
    }

    /**
     * @test
     */
    public function shouldSort()
    {
        $result = FluentIterable::of(['b', 'a', 'c'])
            ->sorted(StringComparator::$BINARY)
            ->toArray();
        self::assertEquals(['a', 'b', 'c'], $result);
    }

    /**
     * @test
     */
    public function shouldRunEach()
    {
        $array = [];
        FluentIterable::of([3, 1, 2])
            ->filter(
                function ($number) {
                    return $number % 2 === 1;
                }
            )
            ->limit(1)
            ->each(
                function ($number) use (&$array) {
                    $array[] = $number;
                }
            );
        self::assertEquals([3], $array);
    }

    /**
     * @test
     */
    public function shouldAppend()
    {
        $result = FluentIterable::of([1, 2])
            ->append(FluentIterable::of([3, 4]))
            ->toArray();
        self::assertEquals([1, 2, 3, 4], $result);
    }

    /**
     * @test
     */
    public function shouldTransformAndConcat()
    {
        $result = FluentIterable::of([1, 2])
            ->transformAndConcat(
                function ($number) {
                    return FluentIterable::of([$number * 2, $number * 3]);
                }
            )
            ->toArray();
        self::assertEquals([2, 3, 4, 6], $result);
    }
}
