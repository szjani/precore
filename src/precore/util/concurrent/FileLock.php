<?php
/*
 * Copyright (c) 2012 Szurovecz János
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

use precore\lang\Object;
use SplFileInfo;

/**
 * @author Szurovecz János <szjani@szjani.hu>
 */
class FileLock extends Object implements Lock
{
    /**
     * @var SplFileInfo
     */
    private $file;

    /**
     * @var resource
     */
    private $fileResource;

    /**
     * @param SplFileInfo $file
     */
    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
    }

    public function isLocked()
    {
        return is_resource($this->fileResource);
    }

    /**
     * @throws LockException
     */
    public function lock()
    {
        $dir = $this->file->getPathInfo();
        if (!$dir->isDir()) {
            @mkdir($dir->getPathname(), 0775, true);
        }
        $this->fileResource = @fopen($this->file->getPathname(), 'a+');
        if (!is_resource($this->fileResource)) {
            $this->fileResource = null;
            $msg = "Could not open file '{$this->file->getPathname()}'!";
            self::getLogger()->error($msg);
            throw new LockException($msg);
        }
        if (!flock($this->fileResource, LOCK_EX | LOCK_NB)) {
            $this->fileResource = null;
            $msg = "Could not lock file '{$this->file->getPathname()}'!";
            self::getLogger()->error($msg);
            throw new LockException($msg);
        }
    }

    /**
     * @throws LockException
     */
    public function unLock()
    {
        if (!$this->isLocked()) {
            $msg = "File '{$this->file->getPathname()}' is not locked!";
            $this->getLogger()->error($msg);
            throw new LockException($msg);
        }
        flock($this->fileResource, LOCK_UN);
        fclose($this->fileResource);
        @unlink($this->file->getPathname());
        $this->fileResource = null;
    }
}
