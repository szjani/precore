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

use PHPUnit_Framework_TestCase;
use SplFileInfo;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class FileLockTest extends PHPUnit_Framework_TestCase
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
