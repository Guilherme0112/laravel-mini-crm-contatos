<?php

namespace App\Core\Domain\Contact\ValueObjects;

use InvalidArgumentException;

final class ContactStatus
{
    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const ACTIVE = 'active';
    public const FAILED = 'failed';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function processing(): self
    {
        return new self(self::PROCESSING);
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function failed(): self
    {
        return new self(self::FAILED);
    }

    public static function fromString(string $status): self
    {
        $status = trim(strtolower($status));

        if (!in_array($status, [self::PENDING, self::PROCESSING, self::ACTIVE, self::FAILED], true)) {
            throw new InvalidArgumentException('Status inválido.');
        }

        return new self($status);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(ContactStatus $status): bool
    {
        return $this->value === $status->value();
    }
}
