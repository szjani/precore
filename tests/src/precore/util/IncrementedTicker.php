<?php

namespace precore\util;

/**
 * Class IncrementedTicker
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class IncrementedTicker extends Ticker
{
    private $counter = 0;

    public static function instance()
    {
        return new IncrementedTicker();
    }

    /**
     * Returns the number of microseconds elapsed since this ticker's fixed point of reference.
     *
     * @return float
     */
    public function read() : float
    {
        return $this->counter++;
    }

    public function counter() : int
    {
        return $this->counter;
    }
}
