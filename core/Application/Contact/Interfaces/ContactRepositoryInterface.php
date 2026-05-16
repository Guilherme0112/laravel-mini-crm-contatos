<?php

namespace App\Core\Application\Contact\Interfaces;

use App\Core\Domain\Contact\Entities\Contact;

interface ContactRepositoryInterface
{
    /**
     * @return Contact[]
     */
    public function all(): array;

    public function paginate(int $perPage = 15, int $page = 1): array;

    public function findById(int $id): ?Contact;

    public function save(Contact $contact): Contact;

    public function update(Contact $contact): Contact;

    public function delete(int $id): void;
}
