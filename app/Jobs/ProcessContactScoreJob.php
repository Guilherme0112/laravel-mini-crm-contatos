<?php

namespace App\Jobs;

use App\Core\Application\Contact\UseCases\ContactUseCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessContactScoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public int $contactId)
    {
    }

    public function handle(ContactUseCase $contactUseCase): void
    {
        $contactUseCase->processContactScore($this->contactId);
    }
}
