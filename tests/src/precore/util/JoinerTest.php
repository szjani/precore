<?php
declare(strict_types=1);

namespace precore\util;

use ArrayIterator;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Class JoinerTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class JoinerTest extends TestCase
{
    const A_SEPARATOR = ',';

    /**
     * @test
     */
    public function shouldReturnSingleElement()
    {
        $element = 'element';
        self::assertEquals($element, Joiner::on(self::A_SEPARATOR)->join([$element]));
    }

    /**
     * @test
     */
    public function shouldNotRewindIterator()
    {
        $it = new ArrayIterator([1, 2, 3]);
        $it->next();
        self::assertEquals('2, 3', Joiner::on(', ')->join($it));
    }

    /**
     * @test
     */
    public function shouldReturnTwoElements()
    {
        $element1 = 'element1';
        $element2 = 'element2';
        self::assertEquals(
            $element1 . self::A_SEPARATOR . $element2,
            Joiner::on(self::A_SEPARATOR)->join([$element1, $element2])
        );
    }

    /**
     * @test
     * @expectedException \precore\lang\NullPointerException
     */
    public function shouldThrowNPEInCaseOfNull()
    {
        $element1 = 'element1';
        $element2 = null;
        Joiner::on(self::A_SEPARATOR)->join([$element1, $element2]);
    }

    /**
     * @test
     */
    public function shouldNotContainNulls()
    {
        $element1 = 'element1';
        $element2 = null;
        self::assertEquals(
            $element1,
            Joiner::on(self::A_SEPARATOR)->skipNulls()->join([$element1, $element2])
        );
    }

    /**
     * @test
     * @expectedException \BadMethodCallException
     */
    public function shouldShouldThrowExceptionIfUseForNullIsCalled()
    {
        Joiner::on(self::A_SEPARATOR)->useForNull('null')->skipNulls();
    }

    /**
     * @test
     */
    public function shouldCreateNewObject()
    {
        $joiner1 = Joiner::on(self::A_SEPARATOR);
        $joiner2 = $joiner1->skipNulls();
        self::assertNotSame($joiner2, $joiner1);
    }

    /**
     * @test
     */
    public function shouldUseToString()
    {
        $date = new DateTime();
        self::assertEquals(ToStringHelper::valueToString($date), Joiner::on(self::A_SEPARATOR)->join([$date]));
    }

    /**
     * @test
     */
    public function shouldHandleIterator()
    {
        $array = ['e1', 'e2'];
        $iterator = new ArrayIterator($array);
        $joiner = Joiner::on(self::A_SEPARATOR);
        self::assertEquals($joiner->join($array), $joiner->join($iterator));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfInvalidInput()
    {
        Joiner::on(self::A_SEPARATOR)->join(1);
    }

    /**
     * @test
     */
    public function shouldContainNullValue()
    {
        $element1 = 'element';
        $element2 = null;
        $nullValue = 'null';
        $joiner = Joiner::on(self::A_SEPARATOR)->useForNull($nullValue);
        $expected = $element1 . self::A_SEPARATOR . $nullValue;
        self::assertEquals($expected, $joiner->join([$element1, $element2]));
    }

    /**
     * @test
     */
    public function shouldBeEqual()
    {
        $joiner1 = Joiner::on(',')->useForNull('null');
        $joiner2 = Joiner::on(',')->useForNull('null');
        self::assertTrue($joiner1->equals($joiner2));
        self::assertFalse($joiner1->equals(Joiner::on(',')->useForNull('other')));
    }

    /**
     * @test
     */
    public function shouldHandleEmptyIterator()
    {
        self::assertEquals('', Joiner::on(',')->join(new \EmptyIterator()));
    }
}
