<?php
declare(strict_types=1);

namespace precore\lang;

class Animal extends Enum
{
    /**
     * @var Animal
     */
    public static $DOG;

    /**
     * @var Animal
     */
    public static $CAT;

    /**
     * @var Animal
     */
    public static $HORSE;
}
Animal::init();
