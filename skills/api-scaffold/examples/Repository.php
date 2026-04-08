<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\LeaseOffer;
use Illuminate\Contracts\Pagination\CursorPaginator;

/**
 * Repository encapsulates all database access for a model.
 * Controllers and Actions never call Eloquent directly.
 */
final class LeaseOfferRepository
{
    public function __construct(
        private readonly LeaseOffer $model,
    ) {}

    /**
     * @param array{
     *   sort_by?: string,
     *   sort_dir?: string,
     *   per_page?: int,
     *   filter_make?: string,
     *   filter_max_rate?: int
     * } $filters
     */
    public function list(array $filters = []): CursorPaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['filter_make'])) {
            $query->where('make', $filters['filter_make']);
        }

        if (isset($filters['filter_max_rate'])) {
            $query->where('monthly_rate', '<=', $filters['filter_max_rate']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        return $query
            ->orderBy($sortBy, $sortDir)
            ->cursorPaginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): LeaseOffer
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(LeaseOffer $offer, array $data): LeaseOffer
    {
        $offer->update($data);

        return $offer->refresh();
    }

    public function delete(LeaseOffer $offer): void
    {
        $offer->delete();
    }
}
