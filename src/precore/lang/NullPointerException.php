<?php
declare(strict_types=1);

namespace precore\lang;

use RuntimeException;

/**
 * Applications should throw instances of this class to indicate other illegal uses of the null object.
 *
 * @package precore\lang
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class NullPointerException extends RuntimeException
{
}
