<?php

namespace App\Http\View\Composers;

use App\Models\Donor;
use Illuminate\View\View;

class DonationEligibilityComposer
{
    /**
     * Available blood groups for filtering
     */
    protected array $bloodGroups = [
        'O-' => 'O Negative (Universal Donor)',
        'O+' => 'O Positive',
        'A-' => 'A Negative',
        'A+' => 'A Positive',
        'B-' => 'B Negative',
        'B+' => 'B Positive',
        'AB-' => 'AB Negative',
        'AB+' => 'AB Positive (Universal Recipient)',
    ];

    /**
     * Bind data to view
     */
    public function compose(View $view): void
    {
        $view->with([
            'bloodGroups' => $this->bloodGroups,
            'cities' => $this->getCities(),
            'donationTypes' => $this->getDonationTypes(),
            'donationStatuses' => $this->getDonationStatuses(),
            'deferralTypes' => $this->getDeferralTypes(),
        ]);
    }

    /**
     * Get available cities from donors
     */
    protected function getCities(): array
    {
        return Donor::query()
            ->where('city', '!=', null)
            ->distinct('city')
            ->pluck('city')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Get available donation types
     */
    protected function getDonationTypes(): array
    {
        return [
            'whole_blood' => 'Whole Blood',
            'plasma' => 'Plasma',
            'platelets' => 'Platelets',
            'red_cells' => 'Red Blood Cells',
        ];
    }

    /**
     * Get available donation statuses
     */
    protected function getDonationStatuses(): array
    {
        return [
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            'deferred' => 'Deferred',
        ];
    }

    /**
     * Get available deferral types
     */
    protected function getDeferralTypes(): array
    {
        return [
            'temporary' => 'Temporary (usually 4 weeks)',
            'permanent' => 'Permanent',
            'conditional' => 'Conditional (until condition resolved)',
        ];
    }
}
