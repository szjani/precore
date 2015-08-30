<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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

    public static function init()
    {
        self::$instance = new ProfilerRegistry();
    }

    public static function instance()
    {
        return self::$instance;
    }

    /**
     * @param $name
     * @return Profiler
     * @throws \OutOfBoundsException if there is no registered profiler with $name
     */
    public function get($name)
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
    public function register($name, Profiler $profiler)
    {
        $this->profilers[$name] = $profiler;
    }
}
ProfilerRegistry::init();
