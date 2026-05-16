<?php

namespace Tests\Unit\Domain;

use App\Core\Domain\Contact\Entities\Contact;
use App\Core\Domain\Contact\ValueObjects\ContactStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    public function test_should_throw_exception_for_invalid_email_format()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Formato de e-mail inválido.');

        Contact::create('Guilherme', 'email-invalido', '123456789');
    }

    public function test_should_mark_contact_active_and_set_processed_at()
    {
        $contact = Contact::create('Guilherme Silva', 'gui@example.com', '11987654321');

        $contact->markAsActive(80);

        $this->assertEquals('active', $contact->getStatus()->value());
        $this->assertEquals(80, $contact->getScore());
        $this->assertInstanceOf(\DateTimeImmutable::class, $contact->getProcessedAt());
    }

    public function test_should_throw_exception_for_invalid_status_value()
    {
        $this->expectException(InvalidArgumentException::class);

        ContactStatus::fromString('invalid-status');
    }
}