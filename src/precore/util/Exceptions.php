<?php
declare(strict_types=1);

namespace precore\util;

use ArrayObject;
use Exception;

/**
 * Utility class for {@link Exception}s.
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Exceptions
{
    private function __construct()
    {
    }

    /**
     * Gets an {@link Exception} cause chain as an {@link ArrayObject}.
     * The first entry in the list will be $exception followed by its cause hierarchy.
     *
     * @param Exception $exception
     * @return ArrayObject
     */
    public static function getCausalChain(Exception $exception) : ArrayObject
    {
        $result = new ArrayObject();
        while ($exception !== null) {
            $result[] = $exception;
            $exception = $exception->getPrevious();
        }
        return $result;
    }

    /**
     * Returns the innermost cause of $exception.
     *
     * @param Exception $exception
     * @return Exception
     */
    public static function getRootCause(Exception $exception) : Exception
    {
        $result = null;
        while ($exception !== null) {
            $result = $exception;
            $exception = $exception->getPrevious();
        }
        return $result;
    }

    /**
     * Propagates throwable exactly as-is, if and only if it is an instance of exceptionClass.
     *
     * @param Exception $exception
     * @param string $exceptionClass
     * @throws Exception the given $exception
     */
    public static function propagateIfInstanceOf(Exception $exception, string $exceptionClass) : void
    {
        if (is_a($exception, $exceptionClass)) {
            throw $exception;
        }
    }
}
