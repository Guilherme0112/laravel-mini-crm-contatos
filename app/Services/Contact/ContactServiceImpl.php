<?php

namespace App\Services\Contact;

use App\Core\Application\Contact\Interfaces\ContactRepositoryInterface;
use App\Core\Application\Contact\UseCases\ContactUseCase;
use App\Core\Domain\Contact\Entities\Contact as ContactEntity;
use App\Events\ContactScoreProcessed;
use App\Core\Domain\Contact\Services\ScoreCalculator;
use App\Core\Domain\Contact\Services\Rules\BrazilianEmailScoreRule;
use App\Core\Domain\Contact\Services\Rules\CorporateEmailScoreRule;
use App\Core\Domain\Contact\Services\Rules\FullNameScoreRule;
use App\Core\Domain\Contact\Services\Rules\PhoneScoreRule;
use InvalidArgumentException;

class ContactServiceImpl implements ContactUseCase
{
    public function __construct(
        private ContactRepositoryInterface $repository
    ) {
    }

    /**
     * @return ContactEntity[]
     */
    public function getContacts(): array
    {
        return $this->repository->all();
    }

    public function paginateContacts(int $perPage = 15, int $page = 1): array
    {
        return $this->repository->paginate($perPage, $page);
    }

    public function getContact(int $contactId): ContactEntity
    {
        $contact = $this->repository->findById($contactId);

        if ($contact === null) {
            throw new InvalidArgumentException('Contato não encontrado.');
        }

        return $contact;
    }

    public function createContact(ContactEntity $contact): ContactEntity
    {
        return $this->repository->save($contact);
    }

    public function updateContact(int $contactId, ContactEntity $contact): ContactEntity
    {
        $existing = $this->repository->findById($contactId);

        if ($existing === null) {
            throw new InvalidArgumentException('Contato não encontrado.');
        }

        $existing->updateDetails($contact->getName(), $contact->getEmail()->value(), $contact->getPhone()->value());

        return $this->repository->update($existing);
    }

    public function deleteContact(int $contactId): void
    {
        $contact = $this->repository->findById($contactId);

        if ($contact === null) {
            throw new InvalidArgumentException('Contato não encontrado.');
        }

        $this->repository->delete($contactId);
    }

    public function processContactScore(int $contactId): ContactEntity
    {
        $contact = $this->repository->findById($contactId);

        if ($contact === null) {
            throw new InvalidArgumentException('Contato não encontrado.');
        }

        $contact->markAsProcessing();
        $this->repository->update($contact);

        sleep(1);

        try {
            $calculator = new ScoreCalculator([
                new CorporateEmailScoreRule(),
                new BrazilianEmailScoreRule(),
                new FullNameScoreRule(),
                new PhoneScoreRule(),
            ]);

            $score = min(100, $calculator->calculate($contact));
            $contact->markAsActive($score);
        } catch (\Throwable $exception) {
            $contact->markAsFailed();
            $this->repository->update($contact);

            throw $exception;
        }

        $processedContact = $this->repository->update($contact);
        event(new ContactScoreProcessed($processedContact));

        return $processedContact;
    }
}
