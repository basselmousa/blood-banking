<?php

namespace App\Repositories;

use App\Models\Donor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;

interface DonorRepositoryInterface
{
    /**
     * Get all eligible donors
     */
    public function getEligible(): Collection;

    /**
     * Get eligible donors filtered by blood group and city
     */
    public function getEligibleByGroup(string $bloodGroup, ?string $city = null): Collection;

    /**
     * Get all deferred donors
     */
    public function getDeferred(): Collection;

    /**
     * Get donor by ID with relationships
     */
    public function findWithRelations(int $id): ?Donor;

    /**
     * Search donors
     */
    public function search(array $filters): Collection;

    /**
     * Get donors with low hemoglobin
     */
    public function getLowHemoglobin(): Collection;

    /**
     * Get donors by blood group
     */
    public function getByBloodGroup(string $bloodGroup): Collection;

    /**
     * Get active donors (not deferred)
     */
    public function getActive(): Collection;
}
