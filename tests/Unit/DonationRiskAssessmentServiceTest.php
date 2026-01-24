<?php

namespace Tests\Unit;

use App\Models\Donor;
use App\Services\DonationRiskAssessmentService;
use Carbon\Carbon;
use Tests\TestCase;

class DonationRiskAssessmentServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DonationRiskAssessmentService();
    }

    /**
     * Test low risk category
     */
    public function test_low_risk_category()
    {
        $donor = Donor::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(30),
            'weight' => 70,
            'height' => 175,
            'hemoglobin_level' => 15.0,
            'last_health_checkup' => Carbon::now()->subMonth(),
        ]);

        $score = $this->service->calculateRiskScore($donor);
        $category = $this->service->getRiskCategory($donor);

        $this->assertLessThan(25, $score);
        $this->assertEquals('low', $category);
    }

    /**
     * Test medium risk category
     */
    public function test_medium_risk_category()
    {
        $donor = Donor::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(22),
            'weight' => 55,
            'height' => 165,
            'hemoglobin_level' => 13.0,
            'last_health_checkup' => Carbon::now()->subMonths(4),
        ]);

        $score = $this->service->calculateRiskScore($donor);
        $category = $this->service->getRiskCategory($donor);

        $this->assertGreaterThanOrEqual(25, $score);
        $this->assertLessThan(50, $score);
        $this->assertEquals('medium', $category);
    }

    /**
     * Test high risk category
     */
    public function test_high_risk_category()
    {
        $donor = Donor::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(65),
            'weight' => 60,
            'height' => 160,
            'hemoglobin_level' => 12.0,
            'last_health_checkup' => null,
        ]);

        $score = $this->service->calculateRiskScore($donor);

        $this->assertGreaterThanOrEqual(50, $score);
    }

    /**
     * Test risk details include risks array
     */
    public function test_risk_details_include_risks_array()
    {
        $donor = Donor::factory()->create([
            'date_of_birth' => Carbon::now()->subYears(25),
            'weight' => 48,
            'height' => 160,
            'last_health_checkup' => null,
        ]);

        $details = $this->service->getRiskDetails($donor);

        $this->assertIsArray($details);
        $this->assertArrayHasKey('score', $details);
        $this->assertArrayHasKey('category', $details);
        $this->assertArrayHasKey('risks', $details);
        $this->assertIsArray($details['risks']);
    }

    /**
     * Test score is between 0 and 100
     */
    public function test_score_is_between_0_and_100()
    {
        $donor = Donor::factory()->create();

        $score = $this->service->calculateRiskScore($donor);

        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }
}
