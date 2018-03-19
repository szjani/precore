<?php
declare(strict_types=1);

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
     * @Around("@execution(precore\util\Profile)")
     * @param MethodInvocation $invocation
     * @return mixed
     */
    public function logMethodProfile(MethodInvocation $invocation)
    {
        /* @var $profileLog Profile */
        $profileLog = $invocation->getMethod()->getAnnotation(Profile::class);
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
