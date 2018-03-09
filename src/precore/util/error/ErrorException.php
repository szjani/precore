<?php
declare(strict_types=1);

namespace precore\util\error;

class ErrorException extends \ErrorException
{
    private $context;

    public function __construct($message, $code, $severity, $filename, $line, array $context)
    {
        parent::__construct($message, $code, $severity, $filename, $line);
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
