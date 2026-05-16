<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Jobs\ProcessContactScoreJob;
use App\Core\Application\Contact\DTOs\ContactData;
use App\Core\Application\Contact\UseCases\ContactUseCase;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ContactController extends Controller
{
    public function __construct(private ContactUseCase $contactUseCase)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        $result = $this->contactUseCase->paginateContacts($perPage, $page);

        return response()->json([
            'data' => ContactResource::collection($result['items']),
            'meta' => $result['meta'],
        ]);
    }

    public function store(StoreContactRequest $request)
    {
        $data = $request->validated();
        $contactData = new ContactData($data['name'], $data['email'], $data['phone']);
        $contact = $this->contactUseCase->createContact($contactData->toEntity());

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function show($id)
    {
        try {
            $contact = $this->contactUseCase->getContact((int) $id);

            return new ContactResource($contact);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }
    }

    public function update(UpdateContactRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $contactData = new ContactData($data['name'], $data['email'], $data['phone']);

            $updatedContact = $this->contactUseCase->updateContact((int) $id, $contactData->toEntity((int) $id));

            return new ContactResource($updatedContact);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $this->contactUseCase->deleteContact((int) $id);

            return response()->json(['message' => 'Contato excluído com sucesso.']);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }
    }

    public function process($id)
    {
        try {
            ProcessContactScoreJob::dispatch((int) $id);

            return response()->json(['message' => 'Processamento do score enfileirado.'], 202);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }
    }
}
