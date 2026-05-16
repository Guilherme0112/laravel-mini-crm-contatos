<?php

namespace App\Core\Domain\Contact\Entities;

use App\Core\Domain\Contact\ValueObjects\ContactStatus;
use App\Core\Domain\Contact\ValueObjects\EmailAddress;
use App\Core\Domain\Contact\ValueObjects\PhoneNumber;
use DateTimeImmutable;

final class Contact
{
    private ?int $id;
    private string $name;
    private EmailAddress $email;
    private PhoneNumber $phone;
    private int $score;
    private ContactStatus $status;
    private ?DateTimeImmutable $processedAt;

    public function __construct(
        ?int $id,
        string $name,
        EmailAddress $email,
        PhoneNumber $phone,
        int $score = 0,
        ?ContactStatus $status = null,
        ?DateTimeImmutable $processedAt = null
    ) {
        $this->id = $id;
        $this->setName($name);
        $this->email = $email;
        $this->phone = $phone;
        $this->score = $score;
        $this->status = $status ?? ContactStatus::pending();
        $this->processedAt = $processedAt;
    }

    public static function create(string $name, string $email, string $phone): self
    {
        return new self(
            null,
            $name,
            new EmailAddress($email),
            new PhoneNumber($phone)
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): EmailAddress
    {
        return $this->email;
    }

    public function getPhone(): PhoneNumber
    {
        return $this->phone;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getStatus(): ContactStatus
    {
        return $this->status;
    }

    public function getProcessedAt(): ?DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function updateDetails(string $name, string $email, string $phone): void
    {
        $this->setName($name);
        $this->email = new EmailAddress($email);
        $this->phone = new PhoneNumber($phone);
    }

    public function markAsProcessing(): void
    {
        $this->status = ContactStatus::processing();
    }

    public function markAsActive(int $score): void
    {
        $this->score = $score;
        $this->status = ContactStatus::active();
        $this->processedAt = new DateTimeImmutable();
    }

    public function markAsFailed(): void
    {
        $this->status = ContactStatus::failed();
        $this->processedAt = new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail()->value(),
            'phone' => $this->getPhone()->value(),
            'score' => $this->getScore(),
            'status' => $this->getStatus()->value(),
            'processed_at' => $this->getProcessedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    private function setName(string $name): void
    {
        $this->name = trim($name);
    }
}
