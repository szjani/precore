<?php

declare(strict_types=1);

namespace precore\util\concurrent;

use PHPUnit\Framework\TestCase;
use SplFileInfo;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class FileLockTest extends TestCase
{
    private $filename;
    private $file;

    public function setUp()
    {
        $this->filename = __DIR__ . '/.lock';
        $this->file = new SplFileInfo($this->filename);
    }

    public function testLock()
    {
        $fileLock = new FileLock($this->file);
        self::assertFalse($fileLock->isLocked());
        $fileLock->lock();
        self::assertTrue($this->file->isFile());
        self::assertTrue($fileLock->isLocked());

        $fileLock->unLock();
        self::assertFalse($fileLock->isLocked());
        self::assertFalse($this->file->isFile());
    }

    public function testLockTwice()
    {
        $fileLock = new FileLock($this->file);
        self::assertFalse($fileLock->isLocked());
        $fileLock->lock();
        self::assertTrue($this->file->isFile());
        self::assertTrue($fileLock->isLocked());

        $fileLock2 = new FileLock($this->file);
        self::assertFalse($fileLock2->isLocked());
        try {
            $fileLock2->lock();
            self::fail("LockException should have been throwed!");
        } catch (LockException $e) {
            $fileLock->unLock();
        }
    }

    /**
     * @expectedException precore\util\concurrent\LockException
     */
    public function testUnlockWithoutLock()
    {
        $fileLock = new FileLock($this->file);
        $fileLock->unLock();
    }

    public function testUseSubdirForLock()
    {
        $file = new SplFileInfo(__DIR__ . '/locks/.lock');
        $fileLock = new FileLock($file);
        $fileLock->lock();
        self::assertTrue($file->isFile());
        self::assertTrue($fileLock->isLocked());

        $fileLock->unLock();
        self::assertFalse($fileLock->isLocked());
        self::assertFalse($file->isFile());
        rmdir($file->getPathInfo()->getPathname());
    }

    public function testFileOpenError()
    {
        $file = new SplFileInfo('//');
        $fileLock = new FileLock($file);
        try {
            $fileLock->lock();
            self::fail("Invalid file resource must cause LockException!");
        } catch (LockException $e) {
        }
        self::assertFalse($fileLock->isLocked());
    }
}
