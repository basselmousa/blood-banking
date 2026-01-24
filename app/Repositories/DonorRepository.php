<?php

namespace App\Repositories;

use App\Models\Donor;
use Illuminate\Database\Eloquent\Collection;

class DonorRepository implements DonorRepositoryInterface
{
    /**
     * Get all eligible donors
     */
    public function getEligible(): Collection
    {
        return Donor::query()
            ->where('is_deferred', false)
            ->where(function ($query) {
                $query->where('deferred_until', '<', now())
                    ->orWhereNull('deferred_until');
            })
            ->whereNull('has_disease')
            ->get();
    }

    /**
     * Get eligible donors filtered by blood group and city
     */
    public function getEligibleByGroup(string $bloodGroup, ?string $city = null): Collection
    {
        $query = Donor::query()
            ->where('blood_group', $bloodGroup)
            ->where('is_deferred', false)
            ->where(function ($q) {
                $q->where('deferred_until', '<', now())
                    ->orWhereNull('deferred_until');
            })
            ->whereNull('has_disease');

        if ($city) {
            $query->where('city', $city);
        }

        return $query->get();
    }

    /**
     * Get all deferred donors
     */
    public function getDeferred(): Collection
    {
        return Donor::query()
            ->where('is_deferred', true)
            ->orWhere('deferred_until', '>=', now())
            ->get();
    }

    /**
     * Get donor by ID with relationships
     */
    public function findWithRelations(int $id): ?Donor
    {
        return Donor::query()
            ->with(['donationRecords', 'donationDeferrals'])
            ->find($id);
    }

    /**
     * Search donors
     */
    public function search(array $filters): Collection
    {
        $query = Donor::query();

        if (isset($filters['name'])) {
            $name = $filters['name'];
            $query->where(function ($q) use ($name) {
                $q->where('first_name', 'LIKE', "%$name%")
                    ->orWhere('last_name', 'LIKE', "%$name%");
            });
        }

        if (isset($filters['blood_group'])) {
            $query->where('blood_group', $filters['blood_group']);
        }

        if (isset($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (isset($filters['is_deferred'])) {
            $query->where('is_deferred', $filters['is_deferred']);
        }

        return $query->get();
    }

    /**
     * Get donors with low hemoglobin
     */
    public function getLowHemoglobin(): Collection
    {
        return Donor::query()
            ->where('hemoglobin', '<', 13.5)
            ->get();
    }

    /**
     * Get donors by blood group
     */
    public function getByBloodGroup(string $bloodGroup): Collection
    {
        return Donor::query()
            ->where('blood_group', $bloodGroup)
            ->get();
    }

    /**
     * Get active donors (not deferred)
     */
    public function getActive(): Collection
    {
        return Donor::query()
            ->where('is_deferred', false)
            ->where(function ($query) {
                $query->where('deferred_until', '<', now())
                    ->orWhereNull('deferred_until');
            })
            ->get();
    }
}
