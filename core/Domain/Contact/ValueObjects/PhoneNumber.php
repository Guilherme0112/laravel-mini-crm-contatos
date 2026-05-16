<?php

namespace App\Core\Domain\Contact\ValueObjects;

use InvalidArgumentException;

final class PhoneNumber
{
    private string $value;

    public function __construct(string $phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) < 8) {
            throw new InvalidArgumentException('Telefone inválido.');
        }

        $this->value = $phone;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function ddd(): ?int
    {
        if (strlen($this->value) === 10 || strlen($this->value) === 11) {
            return (int) substr($this->value, 0, 2);
        }

        return null;
    }

    public function isSaoPaulo(): bool
    {
        $ddd = $this->ddd();

        return $ddd !== null && $ddd >= 11 && $ddd <= 19;
    }
}
