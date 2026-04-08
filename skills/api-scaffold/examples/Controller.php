<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\LeaseOffer\CreateLeaseOfferAction;
use App\Actions\LeaseOffer\DeleteLeaseOfferAction;
use App\Actions\LeaseOffer\ListLeaseOffersAction;
use App\Actions\LeaseOffer\UpdateLeaseOfferAction;
use App\Http\Requests\LeaseOffer\IndexLeaseOfferRequest;
use App\Http\Requests\LeaseOffer\StoreLeaseOfferRequest;
use App\Http\Requests\LeaseOffer\UpdateLeaseOfferRequest;
use App\Http\Resources\LeaseOfferResource;
use App\Models\LeaseOffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Thin controller — delegates everything to Actions.
 * Max 5 lines per method. No business logic here.
 */
final class LeaseOfferController
{
    public function index(IndexLeaseOfferRequest $request, ListLeaseOffersAction $action): AnonymousResourceCollection
    {
        $offers = $action->execute($request->validated());

        return LeaseOfferResource::collection($offers);
    }

    public function store(StoreLeaseOfferRequest $request, CreateLeaseOfferAction $action): JsonResponse
    {
        $offer = $action->execute($request->validated());

        return LeaseOfferResource::make($offer)
            ->response()
            ->setStatusCode(201);
    }

    public function show(LeaseOffer $leaseOffer): LeaseOfferResource
    {
        return LeaseOfferResource::make($leaseOffer);
    }

    public function update(UpdateLeaseOfferRequest $request, LeaseOffer $leaseOffer, UpdateLeaseOfferAction $action): LeaseOfferResource
    {
        $offer = $action->execute($leaseOffer, $request->validated());

        return LeaseOfferResource::make($offer);
    }

    public function destroy(LeaseOffer $leaseOffer, DeleteLeaseOfferAction $action): JsonResponse
    {
        $action->execute($leaseOffer);

        return response()->json(null, 204);
    }
}
