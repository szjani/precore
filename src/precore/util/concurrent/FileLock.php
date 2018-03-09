<?php

declare(strict_types=1);

namespace precore\util\concurrent;

use precore\lang\BaseObject;
use SplFileInfo;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class FileLock extends BaseObject implements Lock
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
            self::getLogger()->error($msg);
            throw new LockException($msg);
        }
        flock($this->fileResource, LOCK_UN);
        fclose($this->fileResource);
        @unlink($this->file->getPathname());
        $this->fileResource = null;
    }
}
