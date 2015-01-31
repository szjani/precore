<?php

namespace precore\util;

use PHPUnit_Framework_TestCase;
use Traversable;

/**
 * Class SplitterTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class SplitterTest extends PHPUnit_Framework_TestCase
{
    const A_SEPARATOR = ',,';

    /**
     * @test
     */
    public function shouldReturnInputIfSeparatorNotFound()
    {
        $input = 'test';
        $result = Splitter::on(self::A_SEPARATOR)->split($input);

        self::assertMatches([$input], $result);
    }

    /**
     * @test
     */
    public function shouldSplitInput()
    {
        $part1 = 'part1';
        $part2 = 'part2';
        $input = $part1 . self::A_SEPARATOR . $part2;
        $result = Splitter::on(self::A_SEPARATOR)->split($input);
        self::assertMatches([$part1, $part2], $result);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfDelimiterIsNotString()
    {
        Splitter::on(1);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfInputIsNotString()
    {
        Splitter::on(self::A_SEPARATOR)->split(1);
    }

    /**
     * @test
     */
    public function shouldTrimResults()
    {
        $input = 'foo,bar,   qux';
        $result = Splitter::on(',')->trimResults()->split($input);
        self::assertMatches(['foo', 'bar', 'qux'], $result);
    }

    /**
     * @test
     */
    public function shouldOmitEmptyStrings()
    {
        $input = 'foo,bar,,qux';
        $result = Splitter::on(',')->omitEmptyStrings()->split($input);
        self::assertMatches(['foo', 'bar', 'qux'], $result);
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
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfPatternIsNotString()
    {
        Splitter::onPattern(1);
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
    public function shouldThrowExceptionIfFixedLengthIsNotNumber()
    {
        Splitter::fixedLength('a');
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
