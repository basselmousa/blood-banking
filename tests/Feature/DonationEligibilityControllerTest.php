<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Donor;
use Carbon\Carbon;
use Tests\TestCase;

class DonationEligibilityControllerTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    /**
     * Test dashboard is accessible
     */
    public function test_eligibility_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.eligibility.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard.donation_eligibility_dashboard');
    }

    /**
     * Test check eligibility is accessible
     */
    public function test_check_eligibility_is_accessible()
    {
        $donor = Donor::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.donors.eligibility', $donor->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard.check_eligibility');
    }

    /**
     * Test list eligible donors
     */
    public function test_list_eligible_donors()
    {
        Donor::factory()->count(5)->create([
            'blood_group' => 'O',
            'is_deferred' => false,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.donors.eligible-list', ['blood_group' => 'O']));

        $response->assertStatus(200);
    }

    /**
     * Test list deferred donors
     */
    public function test_list_deferred_donors()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.donors.deferred-list'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard.deferred_donors_list');
    }

    /**
     * Test defer donor
     */
    public function test_defer_donor()
    {
        $donor = Donor::factory()->create(['is_deferred' => false]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.donors.defer', $donor->id), [
                'reason' => 'recent_tattoo',
                'description' => 'Tattoo applied 2 months ago',
                'deferral_type' => 'temporary',
                'eligible_after' => Carbon::now()->addMonths(4)->format('Y-m-d'),
            ]);

        $response->assertRedirect(route('admin.donors'));
        $this->assertTrue($donor->fresh()->is_deferred);
    }

    /**
     * Test defer donor validation
     */
    public function test_defer_donor_validation()
    {
        $donor = Donor::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.donors.defer', $donor->id), []);

        $response->assertSessionHasErrors(['reason', 'description', 'deferral_type']);
    }

    /**
     * Test clear deferral
     */
    public function test_clear_deferral()
    {
        $donor = Donor::factory()->create([
            'is_deferred' => true,
            'deferral_reason' => 'test',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.donors.clear-deferral', $donor->id));

        $response->assertRedirect();
        $this->assertFalse($donor->fresh()->is_deferred);
    }

    /**
     * Test record donation
     */
    public function test_record_donation()
    {
        $donor = Donor::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.donors.record-donation', $donor->id), [
                'donation_date' => now()->format('Y-m-d H:i'),
                'type' => 'whole_blood',
                'status' => 'completed',
                'blood_volume' => 450,
                'hemoglobin_before' => 14.5,
            ]);

        $response->assertRedirect(route('admin.donors'));
        $this->assertEquals(now()->format('Y-m-d'), $donor->fresh()->last_donation_date->format('Y-m-d'));
    }

    /**
     * Test record donation validation
     */
    public function test_record_donation_validation()
    {
        $donor = Donor::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.donors.record-donation', $donor->id), []);

        $response->assertSessionHasErrors(['donation_date', 'type', 'status']);
    }

    /**
     * Test get donation stats
     */
    public function test_get_donation_stats()
    {
        $donor = Donor::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.donors.stats', $donor->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_donations',
            'donations_last_year',
            'total_blood_volume',
            'avg_hemoglobin',
        ]);
    }

    /**
     * Test unauthenticated user cannot access
     */
    public function test_unauthenticated_user_cannot_access()
    {
        $response = $this->get(route('admin.eligibility.dashboard'));

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.login'));
    }
}
