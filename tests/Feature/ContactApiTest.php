<?php

namespace Tests\Feature;

use App\Jobs\ProcessContactScoreJob;
use App\Models\Contact as ContactModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_show_contact()
    {
        $response = $this->postJson('/api/contacts', [
            'name' => 'Guilherme Silva',
            'email' => 'guilherme@empresa.com.br',
            'phone' => '11987654321',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'Guilherme Silva');
        $response->assertJsonPath('data.status', 'pending');
        $this->assertDatabaseHas('contacts', [
            'email' => 'guilherme@empresa.com.br',
            'status' => 'pending',
        ]);

        $contactId = $response->json('data.id');

        $showResponse = $this->getJson("/api/contacts/{$contactId}");
        $showResponse->assertOk();
        $showResponse->assertJsonPath('data.id', $contactId);
    }

    public function test_can_list_contacts_with_pagination()
    {
        ContactModel::factory()->count(3)->create();

        $response = $this->getJson('/api/contacts?per_page=2');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total', 'last_page']]);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_can_update_contact()
    {
        $contact = ContactModel::factory()->create([
            'name' => 'Guilherme Silva',
            'email' => 'guilherme@empresa.com.br',
            'phone' => '11987654321',
        ]);

        $response = $this->putJson("/api/contacts/{$contact->id}", [
            'name' => 'Guilherme Santos',
            'email' => 'guilherme@empresa.com.br',
            'phone' => '11987654321',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Guilherme Santos');
    }

    public function test_can_soft_delete_contact()
    {
        $contact = ContactModel::factory()->create();

        $response = $this->deleteJson("/api/contacts/{$contact->id}");

        $response->assertOk();
        $this->assertSoftDeleted('contacts', ['id' => $contact->id]);
    }

    public function test_process_score_dispatches_job()
    {
        Queue::fake();

        $contact = ContactModel::factory()->create();

        $response = $this->postJson("/api/contacts/{$contact->id}/process-score");

        $response->assertStatus(202);
        Queue::assertPushed(ProcessContactScoreJob::class, fn ($job) => $job->contactId === $contact->id);
    }
}
