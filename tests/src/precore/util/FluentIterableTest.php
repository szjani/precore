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
        $fluentIterable = FluentIterable::fromArray([1, null, 2, 3])->filter(Predicates::notNull());
        self::assertEquals([1, 2, 3], $fluentIterable->toArray());
        self::assertEquals([1, 2, 3], $fluentIterable->toArray());
    }

    /**
     * @test
     */
    public function shouldLimitItems()
    {
        $result = FluentIterable::fromArray([1, 2, 3])->limit(2)->toArray();
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
        $result = FluentIterable::fromArray([1, 2, 3])->transform($double)->toArray();
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
        $result = FluentIterable::fromArray([1, 2, 3])->transform($double)->filter($smallerThanFive)->toArray();
        self::assertEquals([2, 4], $result);
    }

    /**
     * @test
     */
    public function shouldRemoveZerosAndNulls()
    {
        $result = FluentIterable::fromArray([1, 0, 3, null, 3, 0, 4])
            ->filter(
                Predicates::ands(
                    Predicates::notNull(),
                    Predicates::not(Predicates::equalTo(0))
                )
            )
            ->toArray();
        self::assertEquals([1, 3, 3, 4], $result);
    }
}
