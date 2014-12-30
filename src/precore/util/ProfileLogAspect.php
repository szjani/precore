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

use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;
use SplStack;

/**
 * A Go-AOP aspect which measures and logs the execution time of methods
 * which have the {@link Profile} annotation. It supports nested method chains.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ProfileLogAspect implements Aspect
{
    /**
     * @var SplStack
     */
    private $profilers;

    public function __construct()
    {
        $this->profilers = new SplStack();
    }

    /**
     * @Around("@annotation(precore\util\Profile)")
     * @param MethodInvocation $invocation
     * @return mixed
     */
    public function logMethodProfile(MethodInvocation $invocation)
    {
        /* @var $profileLog Profile */
        $profileLog = $invocation->getMethod()->getAnnotation('\precore\util\Profile');
        $name = $profileLog->name !== null
            ? $profileLog->name
            : $invocation->getMethod()->getName();

        /* @var $profiler Profiler */
        if ($this->profilers->isEmpty()) {
            $profiler = new Profiler($name);
        } else {
            $this->profilers->top()->startNested($name);
            $profiler = ProfilerRegistry::instance()->get($name);
        }

        $this->profilers->push($profiler);
        $profiler->start('exec');
        $result = $invocation->proceed();
        $profiler->stop();
        $this->profilers->pop();

        if ($this->profilers->isEmpty()) {
            $profiler->log();
        }

        return $result;
    }
}
