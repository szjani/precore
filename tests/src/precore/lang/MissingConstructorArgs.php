<?php
declare(strict_types=1);

namespace precore\lang;

class MissingConstructorArgs extends Enum
{
    public static $MISSING;

    protected function __construct($missing)
    {
    }
}
