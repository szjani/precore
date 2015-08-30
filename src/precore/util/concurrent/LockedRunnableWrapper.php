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
use precore\lang\Object;
use precore\lang\RunException;
use precore\lang\Runnable;

/**
 * Useful for cron jobs. The wrapped Runnable object
 * cannot be executed more than once at the same time
 * (if you use the same lock).
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class LockedRunnableWrapper extends Object implements Runnable
{
    /**
     * @var Lock
     */
    private $lock;

    /**
     * @var Runnable
     */
    private $runnable;

    /**
     * @param Runnable $runnable
     * @param Lock $lock
     */
    public function __construct(Runnable $runnable, Lock $lock)
    {
        $this->runnable = $runnable;
        $this->lock = $lock;
    }

    /**
     * It wraps any exceptions coming from inner Runnable object.
     * After the execution the lock become unlocked!
     *
     * @throws RunException
     */
    public function run()
    {
        $thrownException = null;
        try {
            $this->lock->lock();
            try {
                $this->runnable->run();
            } catch (Exception $e) {
                self::getLogger()->error($e);
                $thrownException = $e;
            }
            $this->lock->unLock();
        } catch (LockException $e) {
            throw new RunException('Lock error during running.', null, $e);
        }
        if ($thrownException !== null) {
            throw new RunException('Error during execution wrapped Runnable object.', null, $thrownException);
        }
    }
}
