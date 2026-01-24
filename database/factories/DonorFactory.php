<?php

namespace Database\Factories;

use App\Models\Donor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Donor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $gender = $this->faker->randomElement(['male', 'female']);
        $bloodGroup = $this->faker->randomElement(['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+']);

        return [
            'first_name' => $this->faker->firstName($gender),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->dateTimeBetween('-65 years', '-18 years'),
            'gender' => $gender,
            'blood_group' => $bloodGroup,
            'weight' => $this->faker->numberBetween(55, 120),
            'height' => $this->faker->numberBetween(150, 200),
            'bmi' => $this->faker->randomFloat(2, 18.5, 29.9),
            'hemoglobin' => $gender === 'male'
                ? $this->faker->randomFloat(1, 13.5, 17.5)
                : $this->faker->randomFloat(1, 12.0, 15.5),
            'blood_pressure' => $this->faker->numberBetween(90, 120) . '/' . $this->faker->numberBetween(60, 80),
            'city' => $this->faker->city(),
            'country' => 'Pakistan',
            'has_disease' => false,
            'disease_name' => null,
            'is_deferred' => false,
            'deferred_until' => null,
            'last_donation_date' => $this->faker->dateTimeBetween('-6 months', '-3 months'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the donor is eligible
     */
    public function eligible(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'date_of_birth' => now()->subYears(random_int(25, 50)),
                'weight' => random_int(60, 120),
                'bmi' => random_int(185, 299) / 10,
                'hemoglobin' => $attributes['gender'] === 'male' ? random_int(135, 175) / 10 : random_int(120, 155) / 10,
                'blood_pressure' => '120/80',
                'has_disease' => false,
                'is_deferred' => false,
                'last_donation_date' => now()->subMonths(random_int(3, 6)),
            ];
        });
    }

    /**
     * Indicate that the donor is deferred
     */
    public function deferred($days = 30): self
    {
        return $this->state(function (array $attributes) use ($days) {
            return [
                'is_deferred' => true,
                'deferred_until' => now()->addDays($days),
            ];
        });
    }

    /**
     * Indicate that the donor has a disease
     */
    public function withDisease($diseaseName = 'Diabetes'): self
    {
        return $this->state(function (array $attributes) use ($diseaseName) {
            return [
                'has_disease' => true,
                'disease_name' => $diseaseName,
            ];
        });
    }

    /**
     * Indicate that the donor is too young
     */
    public function tooYoung(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'date_of_birth' => now()->subYears(random_int(15, 17)),
            ];
        });
    }

    /**
     * Indicate that the donor is too old
     */
    public function tooOld(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'date_of_birth' => now()->subYears(random_int(66, 75)),
            ];
        });
    }

    /**
     * Indicate that the donor has low hemoglobin
     */
    public function lowHemoglobin(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'hemoglobin' => $attributes['gender'] === 'male' ? 12.0 : 11.0,
            ];
        });
    }

    /**
     * Indicate that the donor has low weight
     */
    public function lowWeight(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'weight' => 45,
            ];
        });
    }
}
