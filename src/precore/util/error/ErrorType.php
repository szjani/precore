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

use precore\lang\Enum;
use precore\util\Preconditions;

final class ErrorType extends Enum
{
    public static $E_ERROR;
    public static $E_WARNING;
    public static $E_PARSE;
    public static $E_NOTICE;
    public static $E_CORE_ERROR;
    public static $E_CORE_WARNING;
    public static $E_COMPILE_ERROR;
    public static $E_COMPILE_WARNING;
    public static $E_USER_ERROR;
    public static $E_USER_WARNING;
    public static $E_USER_NOTICE;
    public static $E_STRICT;
    public static $E_RECOVERABLE_ERROR;
    public static $E_DEPRECATED;
    public static $E_USER_DEPRECATED;

    private static $types = array();

    protected static function constructorArgs()
    {
        return array(
            'E_ERROR' => array(E_ERROR, 'ErrorException'),
            'E_WARNING' => array(E_WARNING, 'WarningException'),
            'E_PARSE' => array(E_PARSE, 'ParseException'),
            'E_NOTICE' => array(E_NOTICE, 'NoticeException'),
            'E_CORE_ERROR' => array(E_CORE_ERROR, 'CoreErrorException'),
            'E_CORE_WARNING' => array(E_CORE_WARNING, 'CoreWarningException'),
            'E_COMPILE_ERROR' => array(E_COMPILE_ERROR, 'CompileErrorException'),
            'E_COMPILE_WARNING' => array(E_COMPILE_WARNING, 'CompileWarningException'),
            'E_USER_ERROR' => array(E_USER_ERROR, 'UserErrorException'),
            'E_USER_WARNING' => array(E_USER_WARNING, 'UserWarningException'),
            'E_USER_NOTICE' => array(E_USER_NOTICE, 'UserNoticeException'),
            'E_STRICT' => array(E_STRICT, 'StrictException'),
            'E_RECOVERABLE_ERROR' => array(E_RECOVERABLE_ERROR, 'RecoverableErrorException'),
            'E_DEPRECATED' => array(E_DEPRECATED, 'DeprecatedException'),
            'E_USER_DEPRECATED' => array(E_USER_DEPRECATED, 'UserDeprecatedException')
        );
    }

    private $code;
    private $exceptionClass;

    /**
     * @param int $code
     * @param string $exceptionClass
     * @throws \InvalidArgumentException
     */
    private function __construct($code, $exceptionClass)
    {
        $this->code = $code;
        $exceptionClass = __NAMESPACE__ . '\\' . $exceptionClass;
        Preconditions::checkArgument(class_exists($exceptionClass), 'Invalid exception class [%s]', $exceptionClass);
        $this->exceptionClass = $exceptionClass;
        self::$types[$code] = $this;
    }

    /**
     * @param int $number
     * @return ErrorType
     * @throws \OutOfBoundsException
     */
    public static function forCode($number)
    {
        return Preconditions::checkElementExists(self::$types, $number, 'Invalid error code [%s]', $number);
    }

    /**
     * @param $message
     * @param $file
     * @param $line
     * @param array $context
     * @throws ErrorException
     */
    public function throwException($message, $file, $line, array $context)
    {
        $class = $this->exceptionClass;
        throw new $class($message, 0, $this->code, $file, $line, $context);
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string FQCN
     */
    public function getExceptionClass()
    {
        return $this->exceptionClass;
    }
}
ErrorType::init();
