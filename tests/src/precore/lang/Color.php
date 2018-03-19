<?php
declare(strict_types=1);

namespace precore\lang;

/**
 * Description of Color
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class Color extends Enum
{
    const RED_HEX = '#ff0000';
    const BLUE_HEX = '#0000ff';

    /**
     * @var Color
     */
    public static $RED;

    /**
     * @var Color
     */
    public static $BLUE;

    private $hexCode;

    protected static function constructorArgs()
    {
        return [
            'RED' => [self::RED_HEX],
            'BLUE' => [self::BLUE_HEX]
        ];
    }

    protected function __construct($hex)
    {
        $this->hexCode = $hex;
    }

    public function getHexCode()
    {
        return $this->hexCode;
    }
}
Color::init();
