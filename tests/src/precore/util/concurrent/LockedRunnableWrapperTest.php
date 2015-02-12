<?php
/*
 * Copyright (c) 2012 Janos Szurovecz
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

namespace precore\util\concurrent;

use Exception;
use PHPUnit_Framework_TestCase;
use SplFileInfo;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class LockedRunnableWrapperTest extends PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $lock = new FileLock(new SplFileInfo(__DIR__ . '/.LockedRunnableWrapperTest'));
        $runnable = $this->getMock('precore\lang\Runnable');
        $runnable
            ->expects(self::once())
            ->method('run');
        $wrapper = new LockedRunnableWrapper($runnable, $lock);
        $wrapper->run();
        self::assertFalse($lock->isLocked());
    }

    public function testRunWithException()
    {
        $lock = new FileLock(new SplFileInfo(__DIR__ . '/.LockedRunnableWrapperTest'));
        $expectedException = new Exception('Must be thrown!');
        $runnable = $this->getMock('precore\lang\Runnable');
        $runnable
            ->expects(self::once())
            ->method('run')
            ->will(self::throwException($expectedException));
        $wrapper = new LockedRunnableWrapper($runnable, $lock);
        try {
            $wrapper->run();
            self::fail('Exception should have been thrown!');
        } catch (Exception $e) {
            self::assertInstanceOf('precore\lang\RunException', $e);
            self::assertSame($expectedException, $e->getPrevious());
        }
        self::assertFalse($lock->isLocked());
    }

    /**
     * @expectedException precore\lang\RunException
     */
    public function testLockException()
    {
        $lock = $this->getMock(__NAMESPACE__ . '\Lock');
        $lock
            ->expects(self::once())
            ->method('lock')
            ->will(self::throwException(new LockException()));
        $runnable = $this->getMock('precore\lang\Runnable');
        $wrapper = new LockedRunnableWrapper($runnable, $lock);
        $wrapper->run();
    }
}
