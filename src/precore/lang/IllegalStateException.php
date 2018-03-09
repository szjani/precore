<?php
declare(strict_types=1);

namespace precore\lang;

use RuntimeException;

/**
 * Signals that a method has been invoked at an illegal or inappropriate time.
 * In other words, the application is not in an appropriate state for the requested operation.
 *
 * @package precore\lang
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class IllegalStateException extends RuntimeException
{
}
