<?php

namespace Tests\Feature;

use App\Events\ContactScoreProcessed;
use App\Jobs\ProcessContactScoreJob;
use App\Models\Contact as ContactModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProcessContactScoreJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_processes_score_and_dispatches_event()
    {
        Event::fake();

        // Cria o contato no banco com dados específicos para testar as regras
        $contactModel = ContactModel::factory()->create([
            'name' => 'Guilherme Silva', // Nome completo = +10
            'email' => 'guilherme@startup.io', // Email corporativo = +20
            'phone' => '11987654321', // Telefone SP = +20 (Total = 50)
            'score' => 0,
            'status' => 'pending',
            'processed_at' => null,
        ]);

        // Executa o Job de forma síncrona
        ProcessContactScoreJob::dispatchSync($contactModel->id);

        // Verifica se o modelo foi atualizado corretamente no banco de dados
        $contactModel->refresh();
        
        $this->assertEquals('active', $contactModel->status);
        $this->assertEquals(50, $contactModel->score);
        $this->assertNotNull($contactModel->processed_at);

        // Verifica se o evento de broadcast foi disparado corretamente
        Event::assertDispatched(ContactScoreProcessed::class, function ($event) use ($contactModel) {
            return $event->contact->getId() === $contactModel->id 
                && $event->contact->getScore() === 50
                && $event->contact->getStatus()->value() === 'active';
        });
    }
}
