<?php
declare(strict_types=1);

namespace precore\util;

use PHPUnit\Framework\TestCase;
use Traversable;

/**
 * Class SplitterTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class SplitterTest extends TestCase
{
    const A_SEPARATOR = ',,';

    /**
     * @test
     */
    public function shouldReturnInputIfSeparatorNotFound()
    {
        $input = 'test';
        self::assertMatches([$input], Splitter::on(self::A_SEPARATOR)->split($input));
        self::assertMatches([$input], Splitter::on(self::A_SEPARATOR)->eager()->split($input));
    }

    /**
     * @test
     */
    public function shouldSplitInput()
    {
        $part1 = 'part1';
        $part2 = 'part2';
        $input = $part1 . self::A_SEPARATOR . $part2;
        $expected = [$part1, $part2];
        self::assertMatches($expected, Splitter::on(self::A_SEPARATOR)->split($input));
        self::assertMatches($expected, Splitter::on(self::A_SEPARATOR)->eager()->split($input));
    }

    /**
     * @test
     */
    public function shouldTrimResults()
    {
        $input = 'foo,bar,   qux';
        $expected = ['foo', 'bar', 'qux'];
        self::assertMatches($expected, Splitter::on(',')->trimResults()->split($input));
        self::assertMatches($expected, Splitter::on(',')->trimResults()->eager()->split($input));
    }

    /**
     * @test
     */
    public function shouldOmitEmptyStrings()
    {
        $input = 'foo,bar,,qux';
        $expected = ['foo', 'bar', 'qux'];
        self::assertMatches($expected, Splitter::on(',')->omitEmptyStrings()->split($input));
        self::assertMatches($expected, Splitter::on(',')->omitEmptyStrings()->eager()->split($input));
    }

    /**
     * @test
     */
    public function shouldOmitEmptyStringsAndTrimResults()
    {
        $input = 'foo,bar, ,   qux';
        $expected = ['foo', 'bar', 'qux'];

        $result = Splitter::on(',')
            ->trimResults()
            ->omitEmptyStrings()
            ->split($input);
        self::assertMatches($expected, $result);

        $result = Splitter::on(',')
            ->omitEmptyStrings()
            ->trimResults()
            ->split($input);
        self::assertMatches($expected, $result);
    }

    /**
     * @test
     */
    public function shouldSplitOnPatter()
    {
        $input = 'hypertext language, programming';
        $pattern = '/[\s,]+/';
        $result = Splitter::onPattern($pattern)->split($input);
        self::assertMatches(['hypertext', 'language', 'programming'], $result);
    }

    /**
     * @test
     */
    public function shouldSplitOnPatternAndOmitEmptyStringsAndTrimResults()
    {
        $input = 'foo1bar123   qux';
        $pattern = '/\d{1,2}/';
        $result = Splitter::onPattern($pattern)
            ->omitEmptyStrings()
            ->trimResults()
            ->split($input);
        self::assertMatches(['foo', 'bar', 'qux'], $result);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfFixedLengthIsNegative()
    {
        Splitter::fixedLength(-1);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfFixedLengthIsZero()
    {
        Splitter::fixedLength(0);
    }

    /**
     * @test
     */
    public function shouldSplitFixedLength()
    {
        $input = '1234567';
        $result = Splitter::fixedLength(3)->split($input);
        self::assertMatches(['123', '456', '7'], $result);

        $input = '123456';
        $result = Splitter::fixedLength(3)->split($input);
        self::assertMatches(['123', '456'], $result);

        $input = '12345';
        $result = Splitter::fixedLength(3)->split($input);
        self::assertMatches(['123', '45'], $result);
    }

    /**
     * @test
     */
    public function shouldHandleEmptyString()
    {
        self::assertMatches([''], Splitter::on(',')->split(''));
        self::assertMatches([], Splitter::on(',')->omitEmptyStrings()->split(''));
        self::assertMatches([], Splitter::fixedLength(3)->split(''));
    }

    /**
     * @test
     */
    public function shouldTrimAndOmitFixedLengthSplit()
    {
        $input = '12 456   7 ';
        $result = Splitter::fixedLength(3)
            ->trimResults()
            ->omitEmptyStrings()
            ->split($input);
        self::assertMatches(['12', '456', '7'], $result);
    }

    /**
     * @test
     */
    public function shouldEqualWork()
    {
        self::assertTrue(Splitter::on(',')->equals(Splitter::on(',')));
        self::assertTrue(Splitter::fixedLength(3)->equals(Splitter::fixedLength(3)));
        self::assertTrue(Splitter::onPattern('/.*/')->equals(Splitter::onPattern('/.*/')));

        self::assertFalse(Splitter::fixedLength(3)->equals(Splitter::on(',')));
        self::assertFalse(Splitter::onPattern('/.*/')->equals(Splitter::on(',')));
    }

    /**
     * @test
     */
    public function shouldToStringWork()
    {
        self::assertEquals(Splitter::on(',')->toString(), Splitter::on(',')->toString());
        self::assertEquals(Splitter::fixedLength(3)->toString(), Splitter::fixedLength(3)->toString());
        self::assertEquals(Splitter::onPattern('/.*/')->toString(), Splitter::onPattern('/.*/')->toString());
    }

    /**
     * @param array $expected
     * @param Traversable $result
     */
    private static function assertMatches(array $expected, Traversable $result)
    {
        reset($expected);
        $counter = 0;
        foreach ($result as $item) {
            self::assertEquals(current($expected), $item);
            next($expected);
            $counter++;
        }
        self::assertEquals(count($expected), $counter);
    }
}
