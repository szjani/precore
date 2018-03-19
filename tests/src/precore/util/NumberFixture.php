<?php
declare(strict_types=1);

namespace precore\util;

use precore\lang\Enum;

/**
 * Class NumberFixture
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class NumberFixture extends Enum
{
    /**
     * @var NumberFixture
     */
    public static $ONE;

    /**
     * @var NumberFixture
     */
    public static $TWO;
}
NumberFixture::init();
