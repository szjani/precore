<?php
declare(strict_types=1);

namespace precore\util;

use precore\lang\BaseObject;

/**
 * Class ToStringHelperItem
 *
 * @package precore\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ToStringHelperItem extends BaseObject
{
    private $key;
    private $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return string|null
     */
    public function getKey() : ?string
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function toString() : string
    {
        $valueString = ToStringHelper::valueToString($this->value);
        return $this->key === null
            ? $valueString
            : $this->key . '=' . $valueString;
    }
}
