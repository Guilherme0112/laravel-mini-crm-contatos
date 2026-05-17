<?php

namespace Tests\Unit\Application;

use App\Core\Application\Contact\Interfaces\ContactRepositoryInterface;
use App\Core\Domain\Contact\Entities\Contact;
use App\Events\ContactScoreProcessed;
use App\Services\Contact\ContactServiceImpl;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ContactServiceTest extends TestCase
{
    private ContactRepositoryInterface|MockInterface $repository;
    private ContactServiceImpl $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = Mockery::mock(ContactRepositoryInterface::class);
        $this->service = new ContactServiceImpl($this->repository);
    }

    public function test_should_create_contact_via_repository()
    {
        $contact = Contact::create('Guilherme', 'gui@example.com', '11987654321');
        
        $this->repository->shouldReceive('save')
            ->once()
            ->with($contact)
            ->andReturn($contact);

        $result = $this->service->createContact($contact);

        $this->assertEquals($contact, $result);
    }

    public function test_should_update_contact_details_and_save()
    {
        $contactId = 1;
        $existingContact = Contact::create('Guilherme Antigo', 'antigo@example.com', '11900000000');
        $newDetails = Contact::create('Guilherme Novo', 'novo@example.com', '11987654321');
        
        $this->repository->shouldReceive('findById')
            ->once()
            ->with($contactId)
            ->andReturn($existingContact);

        $this->repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function (Contact $c) {
                return $c->getName() === 'Guilherme Novo' &&
                       $c->getEmail()->value() === 'novo@example.com' &&
                       $c->getPhone()->value() === '11987654321';
            }))
            ->andReturn($existingContact);

        $result = $this->service->updateContact($contactId, $newDetails);

        $this->assertEquals('Guilherme Novo', $result->getName());
        $this->assertEquals('novo@example.com', $result->getEmail()->value());
    }

    public function test_should_throw_exception_if_contact_not_found_on_update()
    {
        $contactId = 999;
        $newDetails = Contact::create('Guilherme Novo', 'novo@example.com', '11987654321');
        
        $this->repository->shouldReceive('findById')
            ->once()
            ->with($contactId)
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Contato não encontrado.');

        $this->service->updateContact($contactId, $newDetails);
    }

    public function test_should_process_score_and_dispatch_event_successfully()
    {
        Event::fake();

        $contactId = 1;
        // Email corporativo + telefone SP (+20 + 20 = 40) + Nome completo (+10) = 50 pontos
        $contact = Contact::create('Guilherme Silva', 'gui@empresa.com', '11987654321');

        $this->repository->shouldReceive('findById')
            ->once()
            ->with($contactId)
            ->andReturn($contact);

        // Primeiro update para mudar o status para 'processing'
        $this->repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function (Contact $c) {
                return $c->getStatus()->value() === 'processing';
            }))
            ->andReturn($contact);

        // Segundo update para salvar o score calculado (active)
        $this->repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function (Contact $c) {
                return $c->getStatus()->value() === 'active' && $c->getScore() === 50;
            }))
            ->andReturn($contact);

        $result = $this->service->processContactScore($contactId);

        $this->assertEquals('active', $result->getStatus()->value());
        $this->assertEquals(50, $result->getScore());

        Event::assertDispatched(ContactScoreProcessed::class, function ($event) use ($contact) {
            return $event->contact === $contact;
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
