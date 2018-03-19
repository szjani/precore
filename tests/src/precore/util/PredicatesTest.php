<?php
declare(strict_types=1);

namespace precore\util;

use ArrayObject;
use PHPUnit\Framework\TestCase;

/**
 * Class PredicatesTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class PredicatesTest extends TestCase
{
    /**
     * @test
     */
    public function shouldAndTwoPredicates()
    {
        $predicate = Predicates::ands(Predicates::alwaysTrue(), Predicates::alwaysFalse());
        self::assertFalse(Predicates::call($predicate, true));
        self::assertFalse(Predicates::call($predicate, false));
        self::assertFalse(Predicates::call($predicate, null));

        $predicate = Predicates::ands(Predicates::alwaysTrue(), Predicates::alwaysTrue());
        self::assertTrue(Predicates::call($predicate, true));
        self::assertTrue(Predicates::call($predicate, false));
        self::assertTrue(Predicates::call($predicate, null));
    }

    /**
     * @test
     */
    public function shouldOrPredicates()
    {
        $even = function ($number) {
            return $number % 2 == 0;
        };
        $predicate = Predicates::ors($even, Predicates::isNull());
        self::assertTrue(Predicates::call($predicate, null));
        self::assertTrue(Predicates::call($predicate, 2));
        self::assertFalse(Predicates::call($predicate, 1));
    }

    /**
     * @test
     */
    public function shouldEqualTo()
    {
        self::assertTrue(Predicates::call(Predicates::equalTo(1), 1));
        $uuid1 = UUID::randomUUID();
        $uuid2 = UUID::fromString($uuid1->toString());
        self::assertTrue(Predicates::call(Predicates::equalTo($uuid1), $uuid2));
    }

    /**
     * @test
     */
    public function shouldInstanceOf()
    {
        self::assertTrue(Predicates::call(Predicates::instance(UUID::class), UUID::randomUUID()));
    }

    /**
     * @test
     */
    public function shouldInTraversable()
    {
        $arrayObject = new ArrayObject([1, 2]);
        self::assertTrue(Predicates::call(Predicates::in($arrayObject), 1));
        self::assertTrue(Predicates::call(Predicates::in($arrayObject), 2));
        self::assertFalse(Predicates::call(Predicates::in($arrayObject), 3));
    }

    /**
     * @test
     */
    public function shouldInArray()
    {
        $array = [1, 2];
        self::assertTrue(Predicates::call(Predicates::inArray($array), 1));
        self::assertTrue(Predicates::call(Predicates::inArray($array), 2));
        self::assertFalse(Predicates::call(Predicates::inArray($array), 3));
    }

    /**
     * @test
     */
    public static function shouldMatch()
    {
        self::assertTrue(Predicates::call(Predicates::matches('/^Hello.+/'), 'Hello World!'));
        self::assertFalse(Predicates::call(Predicates::matches('/should not match/'), 'anything'));
    }

    /**
     * @test
     */
    public static function shouldCompose()
    {
        $innerPredicate = Predicates::compose(
            Predicates::equalTo(true),
            function ($element) {
                return (boolean) $element;
            }
        );
        self::assertTrue(Predicates::call($innerPredicate, true));
        self::assertFalse(Predicates::call($innerPredicate, false));
        self::assertFalse(Predicates::call($innerPredicate, 0));
        self::assertTrue(Predicates::call($innerPredicate, 1));
    }
}
