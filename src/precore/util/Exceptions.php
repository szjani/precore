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
    public static function getCausalChain(Exception $exception)
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
    public static function getRootCause(Exception $exception)
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
    public static function propagateIfInstanceOf(Exception $exception, $exceptionClass)
    {
        if (is_a($exception, $exceptionClass)) {
            throw $exception;
        }
    }
}
