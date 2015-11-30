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

use precore\lang\IllegalStateException;
use precore\lang\NullPointerException;
use precore\lang\Object;
use precore\lang\ObjectInterface;

/**
 * An immutable object that may contain a non-null reference to another object. Each
 * instance of this type either contains a non-null reference, or contains nothing (in
 * which case we say that the reference is "absent"); it is never said to "contain null".
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class Optional extends Object
{
    private static $ABSENT;

    public static function init()
    {
        self::$ABSENT = new Absent();
    }

    /**
     * Returns an {@link Optional} instance containing the given non-null reference.
     *
     * @param $instance
     * @return Optional
     * @throws NullPointerException if the given instance is null
     */
    public static function of($instance)
    {
        return new Present($instance);
    }

    /**
     * If nullableReference is non-null, returns an {@link Optional} instance containing that
     * reference; otherwise returns {@link Optional::absent}.
     *
     * @param $nullableReference
     * @return Optional
     */
    public static function ofNullable($nullableReference)
    {
        return $nullableReference === null
            ? Optional::absent()
            : Optional::of($nullableReference);
    }

    /**
     * @return Optional
     */
    public static function absent()
    {
        return self::$ABSENT;
    }

    /**
     * @return mixed the contained instance
     * @throws IllegalStateException if the instance is absent
     */
    public abstract function get();

    /**
     * Returns true if this holder contains a (non-null) instance.
     *
     * @return boolean
     */
    public abstract function isPresent();

    /**
     * If a value is present, invoke the specified consumer with the value, otherwise do nothing.
     *
     * @param callable $consumer
     * @return void
     * @throws NullPointerException if value is present and consumer is null
     */
    public abstract function ifPresent(callable $consumer = null);

    /**
     * Returns the contained instance if it is present; null otherwise. If the
     * instance is known to be present, use {@link get()} instead.
     *
     * @return mixed
     */
    public abstract function orNull();

    /**
     * Returns the contained instance if it is present; defaultValue otherwise.
     *
     * @param $defaultValue
     * @return mixed
     */
    public abstract function orElse($defaultValue);

    /**
     * Return the value if present, otherwise invoke supplier and return the result of that invocation.
     *
     * @param callable $supplier
     * @return mixed
     * @throws NullPointerException if value is not present and other is null
     */
    public abstract function orElseGet(callable $supplier = null);

    /**
     * Return the contained value, if present, otherwise throw an exception to be created by the provided supplier.
     *
     * @param callable $exceptionSupplier
     * @return mixed
     * @throws \Exception provided by $exceptionSupplier
     * @throws NullPointerException if no value is present and $exceptionSupplier is null
     */
    public abstract function orElseThrow(callable $exceptionSupplier = null);

    /**
     * If a value is present, and the value matches the given predicate,
     * return an Optional describing the value, otherwise return an empty Optional.
     *
     * @param callable $predicate
     * @return Optional
     */
    public abstract function filter(callable $predicate);

    /**
     * If a value is present, apply the provided mapping function to it,
     * and if the result is non-null, return an Optional describing the result.
     * Otherwise return an empty Optional.
     *
     * @see Optional::transformer
     * @param callable $mapper
     * @return Optional
     */
    public abstract function map(callable $mapper);

    /**
     * If a value is present, apply the provided Optional-bearing mapping function to it, return that result,
     * otherwise return an empty Optional.
     *
     * @param callable $mapper
     * @return Optional
     */
    public abstract function flatMap(callable $mapper);
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Absent extends Optional
{
    public function get()
    {
        throw new IllegalStateException("This instance is absent");
    }

    public function isPresent()
    {
        return false;
    }

    public function orNull()
    {
        return null;
    }

    public function orElse($defaultValue)
    {
        return $defaultValue;
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof Absent;
    }

    public function filter(callable $predicate)
    {
        return $this;
    }

    public function orElseGet(callable $supplier = null)
    {
        return Functions::call(Preconditions::checkNotNull($supplier));
    }

    public function ifPresent(callable $consumer = null)
    {
    }

    public function toString()
    {
        return $this->getClassName();
    }

    public function flatMap(callable $mapper)
    {
        return $this;
    }

    public function map(callable $mapper)
    {
        return $this;
    }

    public function orElseThrow(callable $exceptionSupplier = null)
    {
        $exception = Functions::call(Preconditions::checkNotNull($exceptionSupplier));
        Preconditions::checkState($exception instanceof \Exception, '$exceptionSupplier must create an Exception');
        throw $exception;
    }
}

/**
 * It is not intended to be used in your code.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Present extends Optional
{
    private $instance;

    public function __construct($instance)
    {
        $this->instance = Preconditions::checkNotNull($instance);
    }

    public function get()
    {
        return $this->instance;
    }

    public function isPresent()
    {
        return true;
    }

    public function orNull()
    {
        return $this->instance;
    }

    public function orElse($defaultValue)
    {
        return $this->instance;
    }

    public function equals(ObjectInterface $object = null)
    {
        return $object instanceof Present
            && Objects::equal($this->instance, $object->instance);
    }

    public function filter(callable $predicate)
    {
        return Predicates::call($predicate, $this->instance)
            ? $this
            : Optional::absent();
    }

    public function orElseGet(callable $supplier = null)
    {
        return $this->instance;
    }

    public function ifPresent(callable $consumer = null)
    {
        Functions::call(Preconditions::checkNotNull($consumer), $this->instance);
    }

    public function toString()
    {
        return Objects::toStringHelper($this)->add($this->instance)->toString();
    }

    public function flatMap(callable $mapper)
    {
        $result = Functions::call($mapper, $this->instance);
        Preconditions::checkState($result instanceof Optional, "result of the mapper must be an Optional");
        return $result;
    }

    public function map(callable $mapper)
    {
        return Optional::ofNullable(Functions::call($mapper, $this->instance));
    }

    public function orElseThrow(callable $exceptionSupplier = null)
    {
        return $this->instance;
    }
}
Optional::init();
