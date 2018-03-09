<?php
declare(strict_types=1);

namespace precore\util;

use Exception;
use precore\lang\NullPointerException;
use precore\lang\BaseObject;
use precore\lang\ObjectInterface;

/**
 * Class TryTo
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class TryTo extends BaseObject
{
    /**
     * Builder method for a try-catch-finally definition.
     *
     * @param Exception[] $exceptions should be caught, all exceptions will be handled if empty
     * @return TryCatch
     */
    public static function catchExceptions(array $exceptions = []) : TryCatch
    {
        return new TryCatch($exceptions);
    }

    /**
     * Executes the given function within a try block, catches all exception types given and runs the finally block.
     *
     * @param callable $tryBlock
     * @param Exception[] $exceptions should be caught, all exceptions will be handled if empty
     * @param callable $finallyBlock
     * @return TryTo
     * @throws Exception the thrown exception if it is not handled by this TryTo
     */
    public static function run(callable $tryBlock, array $exceptions = [], callable $finallyBlock = null) : TryTo
    {
        try {
            return Success::of(Functions::call($tryBlock));
        } catch (Exception $e) {
            if (count($exceptions) === 0) {
                return Failure::of($e);
            }
            $error = FluentIterable::of($exceptions)
                ->filter(Predicates::assignableFrom(get_class($e)))
                ->first();
            if ($error->isPresent()) {
                return Failure::of($e);
            }
            throw $e;
        } finally {
            if ($finallyBlock !== null) {
                Functions::call($finallyBlock);
            }
        }
    }

    /**
     * @param string $exceptionClass
     * @param callable|null $consumer
     * @return TryTo
     */
    public abstract function onFail($exceptionClass = '\Exception', callable $consumer = null) : TryTo;

    /**
     * Map success value. Do nothing if Failure (return this).
     *
     * @param callable|null $mapper
     * @return TryTo
     */
    public abstract function map(callable $mapper = null) : TryTo;

    /**
     * True if Failure / false if Success.
     *
     * @return boolean
     */
    public abstract function isFailure() : bool;

    /**
     * True if Success / false if Failure.
     *
     * @return boolean
     */
    public abstract function isSuccess() : bool;

    /**
     * Optional present if Success, Optional empty if failure
     *
     * @return Optional
     */
    public abstract function toOptional() : Optional;

    /**
     * Optional present if Failure (with Exception), Optional absent if Success.
     *
     * @return Optional
     */
    public abstract function toFailedOptional() : Optional;

    /**
     * Convert a Success to a Failure (with a null value for Exception) if predicate does not hold.
     * Do nothing to a Failure.
     *
     * @param callable|null $predicate
     * @return Optional
     * @throw NullPointerException if Success and predicate is null
     */
    public abstract function filter(callable $predicate = null) : Optional;

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
     * @return TryTo
     */
    public abstract function recoverFor($exceptionClass = '\Exception', callable $recoverFunction = null) : TryTo;

    /**
     * If a value is present, apply the provided mapping function to it, return that result,
     * otherwise return a Failure.
     *
     * @param callable|null $mapper
     * @return TryTo
     * @throws NullPointerException if Success and mapper is null
     */
    public abstract function flatMap(callable $mapper = null) : TryTo;

    /**
     * Throw exception if Failure, do nothing if success.
     *
     * @return void
     * @throws \Exception
     */
    public abstract function throwException() : void;

    /**
     * Flatten a nested TryTo Structure.
     *
     * @return TryTo
     */
    public abstract function flatten() : TryTo;
}

final class Success extends TryTo
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

    public function onFail($exceptionClass = '\Exception', callable $consumer = null) : TryTo
    {
        return $this;
    }

    public function map(callable $mapper = null) : TryTo
    {
        return Success::of(Functions::call(Preconditions::checkNotNull($mapper), $this->value));
    }

    public function get()
    {
        return $this->value;
    }

    public function isFailure() : bool
    {
        return false;
    }

    public function isSuccess() : bool
    {
        return true;
    }

    public function toOptional() : Optional
    {
        return Optional::of($this->value);
    }

    public function filter(callable $predicate = null) : Optional
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

    public function flatMap(callable $mapper = null) : TryTo
    {
        $result = Functions::call(Preconditions::checkNotNull($mapper), $this->value);
        Preconditions::checkState($result instanceof TryTo, "result of the mapper must be a TryTo");
        return $result;
    }

    public function recoverFor($exceptionClass = '\Exception', callable $recoverFunction = null) : TryTo
    {
        return $this;
    }

    public function throwException() : void
    {
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        return $object instanceof Success && $object->value === $this->value;
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)->add($this->value)->toString();
    }

    public function flatten() : TryTo
    {
        return $this->value instanceof TryTo
            ? $this->value->flatten()
            : $this;
    }

    public function toFailedOptional() : Optional
    {
        return Optional::absent();
    }
}

final class Failure extends TryTo
{
    private $exception;

    public static function of(Exception $exception)
    {
        $failure = new Failure();
        $failure->exception = $exception;
        return $failure;
    }

    public function onFail($exceptionClass = '\Exception', callable $consumer = null) : TryTo
    {
        if (is_a($this->exception, $exceptionClass)) {
            Functions::call(Preconditions::checkNotNull($consumer), $this->exception);
        }
        return $this;
    }

    public function map(callable $mapper = null) : TryTo
    {
        return $this;
    }

    public function get()
    {
        throw $this->exception;
    }

    public function isFailure() : bool
    {
        return true;
    }

    public function isSuccess() : bool
    {
        return false;
    }

    public function toOptional() : Optional
    {
        return Optional::absent();
    }

    public function filter(callable $predicate = null) : Optional
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

    public function flatMap(callable $mapper = null) : TryTo
    {
        return $this;
    }

    public function recoverFor($exceptionClass = '\Exception', callable $recoverFunction = null) : TryTo
    {
        return is_a($this->exception, $exceptionClass)
            ? Success::of(Functions::call(Preconditions::checkNotNull($recoverFunction), $this->exception))
            : $this;
    }

    public function throwException() : void
    {
        throw $this->exception;
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        return $object instanceof Failure && $this->exception === $object->exception;
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)->add($this->exception)->toString();
    }

    public function flatten() : TryTo
    {
        return $this;
    }

    public function toFailedOptional() : Optional
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
     * @return TryTo
     * @throws Exception the thrown exception if it is not handled by this TryTo
     */
    public function run(callable $supplier) : TryTo
    {
        return TryTo::run($supplier, $this->exceptions);
    }

    /**
     * @return TryCatch
     */
    public function init() : TryCatch
    {
        return new TryCatch($this->exceptions);
    }
}

final class TryCatch
{
    private $exceptions;

    public function __construct(array $exceptions)
    {
        $this->exceptions = $exceptions;
    }

    /**
     * Runs the given function within a try block.
     *
     * @param callable $tryBlock The function you would like to run in try
     * @return TryTo
     * @throws Exception the thrown exception if it is not handled by this TryTo
     */
    public function run(callable $tryBlock) : TryTo
    {
        return TryTo::run($tryBlock, $this->exceptions);
    }

    /**
     * Builder method, the given function will be executed within a try block.
     *
     * @param callable $tryBlock
     * @return AndFinally
     */
    public function whenRun(callable $tryBlock) : AndFinally
    {
        return new AndFinally($this->exceptions, $tryBlock);
    }
}

final class AndFinally
{
    private $exceptions;
    private $tryBlock;

    public function __construct(array $exceptions, callable $tryBlock)
    {
        $this->exceptions = $exceptions;
        $this->tryBlock = $tryBlock;
    }

    /**
     * Executes the try block provided before and
     * the given function is executed within a finally block.
     *
     * @param callable $finallyBlock
     * @return TryTo
     * @throws Exception the thrown exception if it is not handled by this TryTo
     */
    public function andFinally(callable $finallyBlock) : TryTo
    {
        return TryTo::run($this->tryBlock, $this->exceptions, $finallyBlock);
    }
}
