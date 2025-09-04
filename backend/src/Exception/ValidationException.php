<?php

namespace App\Exception;

class ValidationException extends \Exception
{
    private array $errors;

    public function __construct(string $message = '', array $errors = [], int $code = 400, \Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getViolations(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field][] = $message;
        return $this;
    }

    public function addViolation(string $field, string $message): self
    {
        return $this->addError($field, $message);
    }

    public function toArray(): array
    {
        return [
            'error' => $this->getMessage(),
            'errors' => $this->errors,
            'code' => $this->getCode()
        ];
    }
}
