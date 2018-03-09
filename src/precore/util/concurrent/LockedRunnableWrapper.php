<?php
declare(strict_types=1);

namespace precore\util\concurrent;

use Exception;
use precore\lang\BaseObject;
use precore\lang\RunException;
use precore\lang\Runnable;

/**
 * Useful for cron jobs. The wrapped Runnable object
 * cannot be executed more than once at the same time
 * (if you use the same lock).
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class LockedRunnableWrapper extends BaseObject implements Runnable
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
    public function run() : void
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
            throw new RunException('Lock error during running.', 0, $e);
        }
        if ($thrownException !== null) {
            throw new RunException('Error during execution wrapped Runnable object.', 0, $thrownException);
        }
    }
}
