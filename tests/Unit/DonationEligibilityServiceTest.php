<?php

namespace Tests\Unit;

use App\Models\Donor;
use App\Services\DonationEligibilityService;
use Carbon\Carbon;
use Tests\TestCase;

class DonationEligibilityServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DonationEligibilityService();
    }

    /**
     * Test eligible donor passes all checks
     */
    public function test_eligible_donor_passes_all_checks()
    {
        $donor = Donor::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(30),
            'weight' => 70,
            'height' => 175,
            'hemoglobin_level' => 14.5,
            'last_donation_date' => Carbon::now()->subMonths(6),
            'is_deferred' => false,
            'diseases' => 'None',
        ]);

        $result = $this->service->isEligible($donor);

        $this->assertTrue($result['eligible']);
        $this->assertEmpty($result['issues']);
        $this->assertEquals('low', $result['risk_level']);
    }

    /**
     * Test donor too young is rejected
     */
    public function test_donor_too_young_is_rejected()
    {
        $donor = Donor::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(17),
            'weight' => 70,
            'height' => 175,
        ]);

        $result = $this->service->isEligible($donor);

        $this->assertFalse($result['eligible']);
        $this->assertCount(1, $result['issues']);
        $this->assertEquals('age_too_young', $result['issues'][0]['reason']);
    }

    /**
     * Test donor too old is rejected
     */
    public function test_donor_too_old_is_rejected()
    {
        $donor = Donor::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(70),
            'weight' => 70,
            'height' => 175,
        ]);

        $result = $this->service->isEligible($donor);

        $this->assertFalse($result['eligible']);
    }

    /**
     * Test low BMI is rejected
     */
    public function test_low_bmi_is_rejected()
    {
        $donor = Donor::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(30),
            'weight' => 50,
            'height' => 180,
        ]);

        $result = $this->service->isEligible($donor);

        $this->assertFalse($result['eligible']);
    }

    /**
     * Test low hemoglobin is rejected
     */
    public function test_low_hemoglobin_is_rejected()
    {
        $donor = Donor::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(30),
            'weight' => 70,
            'height' => 175,
            'hemoglobin_level' => 11.0,
            'gender' => 'male',
        ]);

        $result = $this->service->isEligible($donor);

        $this->assertFalse($result['eligible']);
        $this->assertTrue(in_array('hemoglobin_low', array_column($result['issues'], 'reason')));
    }

    /**
     * Test insufficient donation gap is rejected
     */
    public function test_insufficient_donation_gap_is_rejected()
    {
        $donor = Donor::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(30),
            'weight' => 70,
            'height' => 175,
            'hemoglobin_level' => 14.5,
            'last_donation_date' => Carbon::now()->subMonths(1),
        ]);

        $result = $this->service->isEligible($donor);

        $this->assertFalse($result['eligible']);
        $this->assertTrue(in_array('donation_gap_insufficient', array_column($result['issues'], 'reason')));
    }

    /**
     * Test next eligible date is calculated correctly
     */
    public function test_next_eligible_date_is_calculated()
    {
        $lastDonation = Carbon::now()->subMonths(6);
        $donor = Donor::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(30),
            'weight' => 70,
            'height' => 175,
            'last_donation_date' => $lastDonation,
        ]);

        $nextEligible = $this->service->getNextEligibleDate($donor);

        $this->assertInstanceOf(Carbon::class, $nextEligible);
    }

    /**
     * Test deferral creates record
     */
    public function test_deferral_creates_record()
    {
        $donor = Donor::factory()->create();

        $this->service->deferDonor(
            $donor,
            'recent_tattoo',
            'Tattoo applied 2 months ago',
            Carbon::now()->addMonths(4),
            'temporary'
        );

        $this->assertTrue($donor->fresh()->is_deferred);
        $this->assertNotNull($donor->fresh()->deferred_until);
        $this->assertEquals('recent_tattoo', $donor->fresh()->deferral_reason);
    }

    /**
     * Test clearing deferral
     */
    public function test_clearing_deferral()
    {
        $donor = Donor::factory()->create([
            'is_deferred' => true,
            'deferral_reason' => 'test',
        ]);

        $this->service->clearDeferral($donor);

        $this->assertFalse($donor->fresh()->is_deferred);
        $this->assertNull($donor->fresh()->deferral_reason);
    }
}
