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

use ArrayObject;
use PHPUnit_Framework_TestCase;
use precore\lang\IllegalStateException;

/**
 * Class ExceptionsTest
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ExceptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldReturnCausalChain()
    {
        $e1 = new \RuntimeException();
        $e2 = new \InvalidArgumentException('message2', 2, $e1);
        $e3 = new \Exception('message3', 3, $e2);
        self::assertEquals(new ArrayObject([$e3, $e2, $e1]), Exceptions::getCausalChain($e3));
        $iae = Iterables::filterBy(Exceptions::getCausalChain($e3), '\InvalidArgumentException');
        self::assertSame($e2, Iterables::get($iae, 0));
    }

    /**
     * @test
     */
    public function shouldReturnRootCause()
    {
        $e1 = new \RuntimeException();
        $e2 = new \InvalidArgumentException('message2', 2, $e1);
        $e3 = new \Exception('message3', 3, $e2);
        self::assertSame($e1, Exceptions::getRootCause($e3));
    }

    /**
     * @test
     */
    public function shouldNotPropagate()
    {
        Exceptions::propagateIfInstanceOf(new \Exception(), 'RuntimeException');
        self::assertTrue(true);
    }

    /**
     * @test
     * @expectedException \precore\lang\IllegalStateException
     */
    public function shouldPropagate()
    {
        Exceptions::propagateIfInstanceOf(new IllegalStateException(), 'RuntimeException');
    }
}
