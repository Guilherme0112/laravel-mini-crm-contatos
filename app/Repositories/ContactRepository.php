<?php

namespace App\Repositories;

use App\Core\Application\Contact\Interfaces\ContactRepositoryInterface;
use App\Core\Domain\Contact\Entities\Contact as ContactEntity;
use App\Models\Contact as ContactModel;
use DateTimeImmutable;

class ContactRepository implements ContactRepositoryInterface
{
    /**
     * @return ContactEntity[]
     */
    public function all(): array
    {
        return ContactModel::all()
            ->map(fn (ContactModel $model) => $this->mapToEntity($model))
            ->all();
    }

    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $paginator = ContactModel::paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => $paginator->getCollection()
                ->map(fn (ContactModel $model) => $this->mapToEntity($model))
                ->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    public function findById(int $id): ?ContactEntity
    {
        $model = ContactModel::find($id);

        return $model ? $this->mapToEntity($model) : null;
    }

    public function save(ContactEntity $contact): ContactEntity
    {
        $model = ContactModel::create($this->mapToAttributes($contact));

        return $this->mapToEntity($model);
    }

    public function update(ContactEntity $contact): ContactEntity
    {
        $model = ContactModel::findOrFail($contact->getId());
        $model->update($this->mapToAttributes($contact));

        return $this->mapToEntity($model);
    }

    public function delete(int $id): void
    {
        ContactModel::destroy($id);
    }

    private function mapToEntity(ContactModel $model): ContactEntity
    {
        return new ContactEntity(
            $model->id,
            $model->name,
            new \App\Core\Domain\Contact\ValueObjects\EmailAddress($model->email),
            new \App\Core\Domain\Contact\ValueObjects\PhoneNumber($model->phone),
            (int) $model->score,
            \App\Core\Domain\Contact\ValueObjects\ContactStatus::fromString($model->status),
            $model->processed_at ? new DateTimeImmutable($model->processed_at) : null
        );
    }

    private function mapToAttributes(ContactEntity $contact): array
    {
        return [
            'name' => $contact->getName(),
            'email' => $contact->getEmail()->value(),
            'phone' => $contact->getPhone()->value(),
            'score' => $contact->getScore(),
            'status' => $contact->getStatus()->value(),
            'processed_at' => $contact->getProcessedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
