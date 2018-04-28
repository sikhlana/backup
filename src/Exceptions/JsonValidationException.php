<?php

namespace App\Exceptions;

use Throwable;

class JsonValidationException extends \RuntimeException
{
    protected $errors = [];

    public function __construct(string $message = "", array $errors = [], int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}