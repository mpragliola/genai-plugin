<?php

declare(strict_types=1);

namespace App\Actions\LeaseOffer;

use App\Models\LeaseOffer;
use App\Repositories\LeaseOfferRepository;

/**
 * One Action = one responsibility = one public execute() method.
 * Dependencies injected via constructor. Testable in isolation.
 */
final class CreateLeaseOfferAction
{
    public function __construct(
        private readonly LeaseOfferRepository $repository,
    ) {}

    /**
     * @param array{
     *   make: string,
     *   model: string,
     *   monthly_rate: int,
     *   duration_months: int,
     *   mileage_per_year: int,
     *   dealer_id: int
     * } $data
     */
    public function execute(array $data): LeaseOffer
    {
        return $this->repository->create($data);
    }
}
