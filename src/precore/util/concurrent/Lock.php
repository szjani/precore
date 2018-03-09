<?php

declare(strict_types=1);

namespace precore\util\concurrent;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface Lock
{
    /**
     * @throws LockException
     */
    public function lock();

    /**
     * Release the lock.
     *
     * @throws LockException
     */
    public function unLock();

    /**
     * @return boolean
     */
    public function isLocked();
}
