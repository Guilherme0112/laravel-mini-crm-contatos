<?php

namespace App\Core\Application\Contact\UseCases;

use App\Core\Domain\Contact\Entities\Contact;

interface ContactUseCase
{
    /**
     * @return Contact[]
     */
    public function getContacts(): array;

    public function paginateContacts(int $perPage = 15, int $page = 1): array;

    public function getContact(int $contactId): Contact;

    public function createContact(Contact $contact): Contact;

    public function updateContact(int $contactId, Contact $contact): Contact;

    public function deleteContact(int $contactId): void;

    public function processContactScore(int $contactId): Contact;
}