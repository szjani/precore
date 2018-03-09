<?php
declare(strict_types=1);

namespace precore\lang;

/**
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface Runnable
{
    /**
     * @throws RunException
     */
    public function run() : void;
}
