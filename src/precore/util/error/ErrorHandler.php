<?php
declare(strict_types=1);

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
