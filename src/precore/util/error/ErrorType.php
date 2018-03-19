<?php
declare(strict_types=1);

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

    private static $types = [];

    protected static function constructorArgs()
    {
        return [
            'E_ERROR' => [E_ERROR, 'ErrorException'],
            'E_WARNING' => [E_WARNING, 'WarningException'],
            'E_PARSE' => [E_PARSE, 'ParseException'],
            'E_NOTICE' => [E_NOTICE, 'NoticeException'],
            'E_CORE_ERROR' => [E_CORE_ERROR, 'CoreErrorException'],
            'E_CORE_WARNING' => [E_CORE_WARNING, 'CoreWarningException'],
            'E_COMPILE_ERROR' => [E_COMPILE_ERROR, 'CompileErrorException'],
            'E_COMPILE_WARNING' => [E_COMPILE_WARNING, 'CompileWarningException'],
            'E_USER_ERROR' => [E_USER_ERROR, 'UserErrorException'],
            'E_USER_WARNING' => [E_USER_WARNING, 'UserWarningException'],
            'E_USER_NOTICE' => [E_USER_NOTICE, 'UserNoticeException'],
            'E_STRICT' => [E_STRICT, 'StrictException'],
            'E_RECOVERABLE_ERROR' => [E_RECOVERABLE_ERROR, 'RecoverableErrorException'],
            'E_DEPRECATED' => [E_DEPRECATED, 'DeprecatedException'],
            'E_USER_DEPRECATED' => [E_USER_DEPRECATED, 'UserDeprecatedException']
        ];
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
