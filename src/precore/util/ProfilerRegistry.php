<?php
declare(strict_types=1);

namespace precore\util;

/**
 * Contains {@link Profiler} objects.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ProfilerRegistry
{
    /**
     * @var ProfilerRegistry
     */
    private static $instance;

    /**
     * @var Profiler[]
     */
    private $profilers = [];

    public static function init() : void
    {
        self::$instance = new ProfilerRegistry();
    }

    public static function instance() : ProfilerRegistry
    {
        return self::$instance;
    }

    /**
     * @param $name
     * @return Profiler
     * @throws \OutOfBoundsException if there is no registered profiler with $name
     */
    public function get(string $name) : Profiler
    {
        return Preconditions::checkElementExists(
            $this->profilers, $name,
            "There is no registered profiler with name '%s'",
            $name
        );
    }

    /**
     * Should not be called directly. Intended to use from {@link Profiler::startNested()}.
     *
     * @param string $name
     * @param Profiler $profiler
     */
    public function register(string $name, Profiler $profiler) : void
    {
        $this->profilers[$name] = $profiler;
    }
}
ProfilerRegistry::init();
