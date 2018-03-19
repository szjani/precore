<?php
declare(strict_types=1);

namespace precore\lang;

class EmptyConstructorEnum extends Enum
{
    const VALUE = 'hello world';

    public static $ITEM1;

    private $value;

    protected function __construct()
    {
        $this->value = self::VALUE;
    }

    public function getValue()
    {
        return $this->value;
    }
}
EmptyConstructorEnum::init();
