<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Dealer;
use App\Models\LeaseOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test covers the full HTTP lifecycle.
 * Every controller method needs: happy path, validation failure, auth failure, not found.
 */
final class LeaseOfferControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_paginated_offers(): void
    {
        LeaseOffer::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/lease-offers');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'make', 'model', 'monthly_rate']],
                'meta' => ['next_cursor', 'prev_cursor'],
            ]);
    }

    public function test_store_creates_offer_with_valid_data(): void
    {
        $dealer = Dealer::factory()->create();

        $payload = [
            'make' => 'BMW',
            'model' => '320i',
            'monthly_rate' => 399,
            'duration_months' => 36,
            'mileage_per_year' => 15000,
            'dealer_id' => $dealer->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/lease-offers', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.make', 'BMW')
            ->assertJsonPath('data.monthly_rate', 399);

        $this->assertDatabaseHas('lease_offers', ['make' => 'BMW', 'model' => '320i']);
    }

    public function test_store_rejects_invalid_data(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/lease-offers', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['make', 'model', 'monthly_rate']);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/lease-offers', []);

        $response->assertUnauthorized();
    }

    public function test_show_returns_single_offer(): void
    {
        $offer = LeaseOffer::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/lease-offers/{$offer->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $offer->id);
    }

    public function test_show_returns_404_for_missing_offer(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/lease-offers/99999');

        $response->assertNotFound();
    }

    public function test_destroy_removes_offer(): void
    {
        $offer = LeaseOffer::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/lease-offers/{$offer->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('lease_offers', ['id' => $offer->id]);
    }
}
