<?php

declare(strict_types=1);

namespace precore\util\concurrent;

use Exception;
use PHPUnit\Framework\TestCase;
use precore\lang\Runnable;
use SplFileInfo;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class LockedRunnableWrapperTest extends TestCase
{
    public function testRun()
    {
        $lock = new FileLock(new SplFileInfo(__DIR__ . '/.LockedRunnableWrapperTest'));
        $runnable = $this->getMockBuilder(Runnable::class)->getMock();
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
        $runnable = $this->getMockBuilder(Runnable::class)->getMock();
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
     * @expectedException \precore\lang\RunException
     */
    public function testLockException()
    {
        $lock = $this->getMockBuilder(__NAMESPACE__ . '\Lock')->getMock();
        $lock
            ->expects(self::once())
            ->method('lock')
            ->will(self::throwException(new LockException()));
        $runnable = $this->getMockBuilder(Runnable::class)->getMock();
        $wrapper = new LockedRunnableWrapper($runnable, $lock);
        $wrapper->run();
    }
}
