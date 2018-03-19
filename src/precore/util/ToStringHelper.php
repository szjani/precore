<?php
declare(strict_types=1);

namespace precore\util;

use DateTime;
use ErrorException;
use precore\lang\BaseObject;

/**
 * Can be used in ObjectInterface implementations for overriding toString() method.
 *
 * @package precore\util
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ToStringHelper extends BaseObject
{
    private $className;

    /**
     * @var ToStringHelperItem[]
     */
    private $values = [];

    private $omitNullValues = false;

    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public static function valueToString($value) : string
    {
        $stringValue = null;
        if ($value === null) {
            $stringValue = 'null';
        } elseif ($value instanceof DateTime) {
            $stringValue = $value->format(DateTime::ISO8601);
        } elseif (is_array($value)) {
            $parts = [];
            foreach ($value as $key => $valueItem) {
                $parts[] = $key . '=' . self::valueToString($valueItem);
            }
            $stringValue = sprintf('[%s]', implode(', ', $parts));
        } else {
            try {
                $stringValue = (string) $value;
            } catch (ErrorException $e) {
                $stringValue = spl_object_hash($value);
            }
        }
        return $stringValue;
    }

    /**
     * Can be called with 1 or 2 parameters. In the first case only the value,
     * otherwise a key/value pair is passed.
     *
     * @param mixed[] $param
     * @return ToStringHelper $this
     * @throws \InvalidArgumentException if the number of parameters is not 1 or 2
     */
    public function add(...$param) : ToStringHelper
    {
        $count = count($param);
        Preconditions::checkArgument($count == 1 || $count == 2);
        $this->values[] = $count == 2
            ? new ToStringHelperItem($param[0], $param[1])
            : new ToStringHelperItem(null, $param[0]);
        return $this;
    }

    /**
     * Configures the ToStringHelper so toString() will ignore properties with null value.
     *
     * @return ToStringHelper $this
     */
    public function omitNullValues() : ToStringHelper
    {
        $this->omitNullValues = true;
        return $this;
    }

    public function toString() : string
    {
        return sprintf("%s%s", $this->className, $this->membersToString());
    }

    protected function membersToString() : string
    {
        $parts = [];
        foreach ($this->values as $item) {
            if ($this->omitNullValues && $item->getValue() === null) {
                continue;
            }
            $parts[] = $item->toString();
        }
        return sprintf('{%s}', implode(', ', $parts));
    }
}
