<?php
declare(strict_types=1);

namespace App\Security\Infrastructure\Http\Exception;


final class ValidationException extends \InvalidArgumentException
{
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct();
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
