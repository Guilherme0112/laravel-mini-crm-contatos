<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Jobs\ProcessContactScoreJob;
use App\Core\Application\Contact\DTOs\ContactData;
use App\Core\Application\Contact\UseCases\ContactUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;

class ContactController extends Controller
{
    public function __construct(private ContactUseCase $contactUseCase)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);
        $result = $this->contactUseCase->paginateContacts($perPage, $page);
        return response()->json([
            'data' => ContactResource::collection($result['items']),
            'meta' => $result['meta'],
        ]);
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $data = $request->validated();
        $contactData = new ContactData($data['name'], $data['email'], $data['phone']);
        $contact = $this->contactUseCase->createContact($contactData->toEntity());
        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function show($id): ContactResource|JsonResponse
    {
        try {
            $contact = $this->contactUseCase->getContact((int) $id);
            return new ContactResource($contact);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }
    }

    public function update(UpdateContactRequest $request, $id): ContactResource|JsonResponse
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

    public function destroy($id): Response|JsonResponse
    {
        try {
            $this->contactUseCase->deleteContact((int) $id);
            return response()->noContent();
        } catch (InvalidArgumentException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }
    }

    public function process($id): JsonResponse
    {
        try {
            ProcessContactScoreJob::dispatch((int) $id);
            return response()->json(['message' => 'Processamento do score enfileirado.'], 202);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['error' => $exception->getMessage()], 404);
        }
    }
}
