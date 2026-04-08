<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms model data for API output.
 * Never expose internal fields (password, remember_token, etc.)
 * Always use explicit field listing, not toArray().
 */
final class LeaseOfferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'make' => $this->make,
            'model' => $this->model,
            'monthly_rate' => $this->monthly_rate,
            'duration_months' => $this->duration_months,
            'mileage_per_year' => $this->mileage_per_year,
            'dealer_id' => $this->dealer_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
