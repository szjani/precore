<?php
/*
 * Copyright (c) 2012-2015 Janos Szurovecz
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

use Exception;
use precore\lang\NullPointerException;
use precore\lang\Object;
use precore\lang\ObjectInterface;

/**
 * Class Trying
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class Trying extends Object
{
    /**
     * @param Exception[] $exceptions
     * @return Init
     */
    public static function catchExceptions(array $exceptions = [])
    {
        return new Init($exceptions);
    }

    /**
     * @param callable $runnable
     * @param array $exceptions
     * @return Trying
     * @throws Exception the thrown exception if it is not handled by this Trying
     */
    public static function runWithCatch(callable $runnable, array $exceptions = [])
    {
        try {
            return Success::of(Functions::call($runnable));
        } catch (Exception $e) {
            $error = FluentIterable::of($exceptions)
                ->filter(Predicates::assignableFrom(get_class($e)))
                ->first();
            if ($error->isPresent()) {
                return Failure::of($e);
            }
            throw $e;
        }
    }

    /**
     * @param string $exceptionClass
     * @param callable|null $consumer
     * @return Trying
     */
    public abstract function onFail($exceptionClass = '\Exception', callable $consumer = null);

    /**
     * Map success value. Do nothing if Failure (return this).
     *
     * @param callable|null $mapper
     * @return Trying
     */
    public abstract function map(callable $mapper = null);

    /**
     * True if Failure / false if Success.
     *
     * @return boolean
     */
    public abstract function isFailure();

    /**
     * True if Success / false if Failure.
     *
     * @return boolean
     */
    public abstract function isSuccess();

    /**
     * Optional present if Success, Optional empty if failure
     *
     * @return Optional
     */
    public abstract function toOptional();

    /**
     * Optional present if Failure (with Exception), Optional absent if Success.
     *
     * @return Optional
     */
    public abstract function toFailedOptional();

    /**
     * Convert a Success to a Failure (with a null value for Exception) if predicate does not hold.
     * Do nothing to a Failure.
     *
     * @param callable|null $predicate
     * @return Optional
     * @throw NullPointerException if Success and predicate is null
     */
    public abstract function filter(callable $predicate = null);

    /**
     * Get the contained value.
     *
     * @return mixed value
     * @throws Exception the contained exception if it is a Failure
     */
    public abstract function get();

    /**
     * Return value supplied if Failure, otherwise return Success value.
     *
     * @param $defaultValue
     * @return mixed
     */
    public abstract function orElse($defaultValue);

    /**
     * Return the value if Success, otherwise invoke supplier and return the result of that invocation.
     *
     * @param callable|null $supplier
     * @return mixed
     * @throws NullPointerException if Failure and supplier is null
     */
    public abstract function orElseGet(callable $supplier = null);

    /**
     * Recovery function - map from a failure to a Success.
     *
     * @param callable|null $recoverFunction
     * @return Success
     */
    public abstract function recover(callable $recoverFunction = null);

    /**
     * Recover if exception is of specified type.
     *
     * @param string $exceptionClass
     * @param callable|null $recoverFunction
     * @return Trying
     */
    public abstract function recoverFor($exceptionClass = '\Exception', callable $recoverFunction = null);

    /**
     * If a value is present, apply the provided mapping function to it, return that result,
     * otherwise return a Failure.
     *
     * @param callable|null $mapper
     * @return Trying
     * @throws NullPointerException if Success and mapper is null
     */
    public abstract function flatMap(callable $mapper = null);

    /**
     * Throw exception if Failure, do nothing if success.
     *
     * @return void
     * @throws \Exception
     */
    public abstract function throwException();

    /**
     * Flatten a nested Trying Structure.
     *
     * @return Trying
     */
    public abstract function flatten();
}

final class Success extends Trying
{
    private $value;

    private function __construct($value)
    {
        $this->value = $value;
    }

    public static function of($value)
    {
        return new Success($value);
    }

    public function onFail($exceptionClass = '\Exception', callable $consumer = null)
    {
        return $this;
    }

    public function map(callable $mapper = null)
    {
        return Success::of(Functions::call(Preconditions::checkNotNull($mapper), $this->value));
    }

    public function get()
    {
        return $this->value;
    }

    public function isFailure()
    {
        return false;
    }

    public function isSuccess()
    {
        return true;
    }

    public function toOptional()
    {
        return Optional::of($this->value);
    }

    public function filter(callable $predicate = null)
    {
        return Predicates::call(Preconditions::checkNotNull($predicate), $this->value)
            ? Optional::of($this->value)
            : Optional::absent();
    }

    public function orElse($defaultValue)
    {
        return $this->value;
    }

    public function recover(callable $recoverFunction = null)
    {
        return $this;
    }

    public function orElseGet(callable $supplier = null)
    {
        return $this->value;
    }

    public function flatMap(callable $mapper = null)
    {
        $result = Functions::call(Preconditions::checkNotNull($mapper), $this->value);
        Preconditions::checkState($result instanceof Trying, "result of the mapper must be a Trying");
        return $result;
    }

    public function recoverFor($exceptionClass = '\Exception', callable $recoverFunction = null)
    {
        return $this;
    }

    public function throwException()
    {
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof Success && $object->value === $this->value;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)->add($this->value)->toString();
    }

    public function flatten()
    {
        return $this->value instanceof Trying
            ? $this->value->flatten()
            : $this;
    }

    public function toFailedOptional()
    {
        return Optional::absent();
    }
}

final class Failure extends Trying
{
    private $exception;

    public static function of(Exception $exception)
    {
        $failure = new Failure();
        $failure->exception = $exception;
        return $failure;
    }

    public function onFail($exceptionClass = '\Exception', callable $consumer = null)
    {
        if (is_a($this->exception, $exceptionClass)) {
            Functions::call(Preconditions::checkNotNull($consumer), $this->exception);
        }
        return $this;
    }

    public function map(callable $mapper = null)
    {
        return $this;
    }

    public function get()
    {
        throw $this->exception;
    }

    public function isFailure()
    {
        return true;
    }

    public function isSuccess()
    {
        return false;
    }

    public function toOptional()
    {
        return Optional::absent();
    }

    public function filter(callable $predicate = null)
    {
        return Optional::absent();
    }

    public function orElse($defaultValue)
    {
        return $defaultValue;
    }

    public function recover(callable $recoverFunction = null)
    {
        return Success::of(Functions::call(Preconditions::checkNotNull($recoverFunction), $this->exception));
    }

    public function orElseGet(callable $supplier = null)
    {
        return Functions::call(Preconditions::checkNotNull($supplier));
    }

    public function flatMap(callable $mapper = null)
    {
        return $this;
    }

    public function recoverFor($exceptionClass = '\Exception', callable $recoverFunction = null)
    {
        return is_a($this->exception, $exceptionClass)
            ? Success::of(Functions::call(Preconditions::checkNotNull($recoverFunction), $this->exception))
            : $this;
    }

    public function throwException()
    {
        throw $this->exception;
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof Failure && $this->exception === $object->exception;
    }

    public function toString()
    {
        return Objects::toStringHelper($this)->add($this->exception)->toString();
    }

    public function flatten()
    {
        return $this;
    }

    public function toFailedOptional()
    {
        return Optional::of($this->exception);
    }
}

final class Init
{
    private $exceptions;

    public function __construct(array $exceptions)
    {
        $this->exceptions = $exceptions;
    }

    /**
     * @param callable $supplier The function you would like to run in try
     * @return Trying
     * @throws Exception the thrown exception if it is not handled by this Trying
     */
    public function tryThis(callable $supplier)
    {
        return Trying::runWithCatch($supplier, $this->exceptions);
    }
}
