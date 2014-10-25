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

namespace precore\util\error;

/**
 * Helper class to be able to catch errors as exceptions.
 *
 * @package precore\util\error
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 * @see http://hu1.php.net/manual/en/function.set-error-handler.php#112881
 */
final class ErrorHandler
{
    private function __construct()
    {
    }

    /**
     * Converts all errors to the proper exception.
     */
    public static function register()
    {
        set_error_handler(
            function ($code, $message, $file, $line, $context) {
                if (error_reporting() == 0) {
                    return false;
                }
                ErrorType::forCode($code)->throwException($message, $file, $line, $context);
            }
        );
    }
}
