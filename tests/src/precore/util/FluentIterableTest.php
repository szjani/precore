<?php

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
