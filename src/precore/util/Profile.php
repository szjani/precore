<?php
declare(strict_types=1);

namespace precore\util;

/**
 * Annotation for profiling.
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Profile
{
    /**
     * @var string
     */
    public $name = null;
}
