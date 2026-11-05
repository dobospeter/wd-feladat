<?php

declare(strict_types=1);

namespace Inventory\Contract\Presentation\Http\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Inventory\Contract\Application\UseCase\CreateContract;
use Inventory\Contract\Application\UseCase\DeleteContract;
use Inventory\Contract\Application\UseCase\GetContract;
use Inventory\Contract\Application\UseCase\ListContracts;
use Inventory\Contract\Application\UseCase\UpdateContract;
use Inventory\Contract\Domain\ContractId;
use Inventory\Contract\Presentation\Http\Request\SaveContractRequest;
use Inventory\Contract\Presentation\Http\Resource\ContractDetailsResource;
use Inventory\Contract\Presentation\Http\Resource\ContractResource;
use Inventory\Shared\Presentation\Http\Request\PaginationRequest;
use Inventory\Shared\Presentation\Http\Response\PaginatedCollection;

final class ContractController
{
    public function index(PaginationRequest $request, ListContracts $listContracts): AnonymousResourceCollection
    {
        return PaginatedCollection::from($listContracts->handle($request->toPageRequest()), ContractResource::class);
    }

    public function store(SaveContractRequest $request, CreateContract $createContract): JsonResponse
    {
        $details = $createContract->handle($request->toData());

        return (new ContractDetailsResource($details))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('contracts.show', ['contract' => $details->contract->id()->value]));
    }

    public function show(string $contract, GetContract $getContract): ContractDetailsResource
    {
        return new ContractDetailsResource($getContract->handle(ContractId::fromString($contract)));
    }

    public function update(string $contract, SaveContractRequest $request, UpdateContract $updateContract): ContractDetailsResource
    {
        return new ContractDetailsResource(
            $updateContract->handle(ContractId::fromString($contract), $request->toData()),
        );
    }

    public function destroy(string $contract, DeleteContract $deleteContract): Response
    {
        $deleteContract->handle(ContractId::fromString($contract));

        return response()->noContent();
    }
}
