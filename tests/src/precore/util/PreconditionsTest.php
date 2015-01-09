<?php

namespace precore\util;

use PHPUnit_Framework_TestCase;

/**
 * Class PreconditionsTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class PreconditionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfInvalidArgument()
    {
        Preconditions::checkArgument(false);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ouch
     */
    public function shouldThrowExceptionWithMessageIfInvalidArgument()
    {
        Preconditions::checkArgument(false, 'ouch');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage HelloWorld!
     */
    public function shouldThrowExceptionWithParametrizedMessageIfInvalidArgument()
    {
        Preconditions::checkArgument(false, '%s%s!', 'Hello', 'World');
    }

    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     * @expectedExceptionMessage Expected i < j, but 2 > 1
     */
    public function shouldThrowExceptionWithParametrizedMessageIfIllegalState()
    {
        $i = 2;
        $j = 1;
        Preconditions::checkState($i < $j, "Expected i < j, but %s > %s", $i, $j);
    }

    /**
     * @test
     * @expectedException \precore\lang\NullPointerException
     * @expectedExceptionMessage i is null
     */
    public function shouldThrowExceptionWithParametrizedMessageIfNull()
    {
        $i = null;
        Preconditions::checkNotNull($i, 'i %s null', 'is');
    }

    /**
     * @test
     */
    public function shouldReturnNotNullVariable()
    {
        $i = 2;
        self::assertEquals($i, Preconditions::checkNotNull($i));
    }

    /**
     * @test
     */
    public function shouldReturnElementIfExists()
    {
        $element = 'hello';
        $messages = ['key' => $element];
        self::assertEquals($element, Preconditions::checkElementExists($messages, 'key'));
    }

    /**
     * @test
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Index 1 cannot be found
     */
    public function shouldThrowExceptionIfKeyDoesNotExist()
    {
        $index = 1;
        Preconditions::checkElementExists([], $index, "Index %d cannot be found", $index);
    }
}
