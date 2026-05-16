<?php

namespace Tests\Unit\Domain;

use App\Core\Domain\Contact\Entities\Contact;
use App\Core\Domain\Contact\ValueObjects\ContactStatus;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    public function test_should_create_new_contact_with_default_values()
    {
        $contact = Contact::create('Guilherme Silva ', 'gui@example.com', '11987654321');

        $this->assertNull($contact->getId());
        // Verifica se o trim() funcionou
        $this->assertEquals('Guilherme Silva', $contact->getName()); 
        $this->assertEquals('gui@example.com', $contact->getEmail()->value());
        $this->assertEquals('11987654321', $contact->getPhone()->value());
        
        // Regras de negócio iniciais
        $this->assertEquals(0, $contact->getScore());
        $this->assertEquals('pending', $contact->getStatus()->value());
        $this->assertNull($contact->getProcessedAt());
    }

    public function test_should_update_contact_details()
    {
        $contact = Contact::create('Guilherme', 'gui@example.com', '11987654321');

        $contact->updateDetails('Novo Nome ', 'novo@example.com', '11900000000');

        $this->assertEquals('Novo Nome', $contact->getName());
        $this->assertEquals('novo@example.com', $contact->getEmail()->value());
        $this->assertEquals('11900000000', $contact->getPhone()->value());
    }

    public function test_should_mark_contact_as_processing()
    {
        $contact = Contact::create('Guilherme', 'gui@example.com', '11987654321');

        $contact->markAsProcessing();

        $this->assertEquals('processing', $contact->getStatus()->value());
        $this->assertNull($contact->getProcessedAt()); // Só deve preencher ao finalizar
    }

    public function test_should_mark_contact_as_active_and_set_processed_at()
    {
        $contact = Contact::create('Guilherme', 'gui@example.com', '11987654321');

        $contact->markAsActive(85);

        $this->assertEquals('active', $contact->getStatus()->value());
        $this->assertEquals(85, $contact->getScore());
        $this->assertInstanceOf(\DateTimeImmutable::class, $contact->getProcessedAt());
    }

    public function test_should_mark_contact_as_failed_and_set_processed_at()
    {
        $contact = Contact::create('Guilherme', 'gui@example.com', '11987654321');

        $contact->markAsFailed();

        $this->assertEquals('failed', $contact->getStatus()->value());
        $this->assertInstanceOf(\DateTimeImmutable::class, $contact->getProcessedAt());
    }

    public function test_should_convert_contact_to_array()
    {
        $contact = Contact::create('Guilherme', 'gui@example.com', '11987654321');
        $contact->markAsActive(100);

        $array = $contact->toArray();

        $this->assertIsArray($array);
        $this->assertNull($array['id']);
        $this->assertEquals('Guilherme', $array['name']);
        $this->assertEquals('gui@example.com', $array['email']);
        $this->assertEquals('11987654321', $array['phone']);
        $this->assertEquals(100, $array['score']);
        $this->assertEquals('active', $array['status']);
        $this->assertNotNull($array['processed_at']);
    }

    public function test_should_throw_exception_for_invalid_email_format()
    {
        $this->expectException(InvalidArgumentException::class);
        
        Contact::create('Guilherme', 'email-invalido', '123456789');
    }

    public function test_should_throw_exception_for_invalid_status_value()
    {
        $this->expectException(InvalidArgumentException::class);
        
        ContactStatus::fromString('invalid-status');
    }
}