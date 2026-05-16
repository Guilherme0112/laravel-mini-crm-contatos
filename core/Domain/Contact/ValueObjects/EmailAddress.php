<?php

namespace App\Core\Domain\Contact\ValueObjects;

use InvalidArgumentException;

final class EmailAddress
{
    private string $value;

    public function __construct(string $email)
    {
        $email = trim(strtolower($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Formato de e-mail inválido.');
        }

        $this->value = $email;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function domain(): string
    {
        return substr(strrchr($this->value, '@'), 1) ?: '';
    }

    public function isCorporate(): bool
    {
        $personalDomains = ['gmail.com', 'hotmail.com', 'yahoo.com'];

        return !in_array($this->domain(), $personalDomains, true);
    }

    public function isBrazilian(): bool
    {
        return str_ends_with($this->domain(), '.br');
    }
}
