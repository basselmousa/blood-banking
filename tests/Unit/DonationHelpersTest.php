<?php

namespace Tests\Unit;

use App\Helpers\BloodCompatibilityHelper;
use App\Helpers\DonationCriteriaHelper;
use Carbon\Carbon;
use Tests\TestCase;

class DonationHelpersTest extends TestCase
{
    /**
     * Test blood compatibility for O negative (universal donor)
     */
    public function test_o_negative_is_universal_donor()
    {
        $compatible = BloodCompatibilityHelper::getCompatibleDonors('O', '-');

        $this->assertCount(1, $compatible);
        $this->assertContains('O-', $compatible);
    }

    /**
     * Test AB positive (universal recipient)
     */
    public function test_ab_positive_can_receive_all()
    {
        $recipients = BloodCompatibilityHelper::getRecipientBloodGroups('AB', '+');

        $this->assertCount(1, $recipients);
        $this->assertContains('AB+', $recipients);
    }

    /**
     * Test age validation
     */
    public function test_age_validation()
    {
        $this->assertTrue(DonationCriteriaHelper::isAgeValid(25));
        $this->assertFalse(DonationCriteriaHelper::isAgeValid(17));
        $this->assertFalse(DonationCriteriaHelper::isAgeValid(70));
    }

    /**
     * Test weight validation
     */
    public function test_weight_validation()
    {
        $this->assertTrue(DonationCriteriaHelper::isWeightValid(60));
        $this->assertFalse(DonationCriteriaHelper::isWeightValid(40));
        $this->assertTrue(DonationCriteriaHelper::isWeightValid(null));
    }

    /**
     * Test BMI calculation
     */
    public function test_bmi_calculation()
    {
        $bmi = DonationCriteriaHelper::calculateBMI(70, 175);

        $this->assertAlmostEquals(22.86, $bmi, 0.1);
    }

    /**
     * Test healthy BMI range
     */
    public function test_healthy_bmi_range()
    {
        $this->assertTrue(DonationCriteriaHelper::isBMIHealthy(22.0));
        $this->assertFalse(DonationCriteriaHelper::isBMIHealthy(18.0));
        $this->assertFalse(DonationCriteriaHelper::isBMIHealthy(30.0));
    }

    /**
     * Test hemoglobin validation
     */
    public function test_hemoglobin_validation()
    {
        $this->assertTrue(DonationCriteriaHelper::isHemoglobinSufficient(14.0, 'male'));
        $this->assertFalse(DonationCriteriaHelper::isHemoglobinSufficient(13.0, 'male'));
        $this->assertTrue(DonationCriteriaHelper::isHemoglobinSufficient(13.0, 'female'));
    }

    /**
     * Test donation gap calculation
     */
    public function test_donation_gap_calculation()
    {
        $lastDonation = Carbon::now()->subMonths(6);

        $this->assertTrue(DonationCriteriaHelper::isDonationGapSufficient($lastDonation));
        $this->assertFalse(DonationCriteriaHelper::isDonationGapSufficient(Carbon::now()->subMonths(1)));
        $this->assertTrue(DonationCriteriaHelper::isDonationGapSufficient(null));
    }

    /**
     * Test disqualifying condition check
     */
    public function test_disqualifying_condition_check()
    {
        $this->assertTrue(DonationCriteriaHelper::hasDisqualifyingCondition('HIV, some other condition'));
        $this->assertTrue(DonationCriteriaHelper::hasDisqualifyingCondition('hepatitis_b'));
        $this->assertFalse(DonationCriteriaHelper::hasDisqualifyingCondition('common cold, asthma'));
    }

    /**
     * Test min hemoglobin by gender
     */
    public function test_min_hemoglobin_by_gender()
    {
        $this->assertEquals(13.5, DonationCriteriaHelper::getMinHemoglobin('male'));
        $this->assertEquals(12.5, DonationCriteriaHelper::getMinHemoglobin('female'));
    }
}
