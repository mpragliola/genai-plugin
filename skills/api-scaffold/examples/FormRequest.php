<?php

declare(strict_types=1);

namespace App\Http\Requests\LeaseOffer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation lives here, not in controllers or actions.
 * authorize() handles auth checks. rules() handles input validation.
 */
final class StoreLeaseOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\LeaseOffer::class) ?? false;
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'monthly_rate' => ['required', 'integer', 'min:1', 'max:99999'],
            'duration_months' => ['required', 'integer', 'in:12,24,36,48'],
            'mileage_per_year' => ['required', 'integer', 'min:5000', 'max:100000'],
            'dealer_id' => ['required', 'integer', 'exists:dealers,id'],
        ];
    }
}
