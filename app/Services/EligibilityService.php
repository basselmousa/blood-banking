<?php

namespace App\Services;

use App\Models\EligibilityTemplate;

class EligibilityService
{
    public function createEligibilityTemplate($tenantId, array $data)
    {
        return EligibilityTemplate::create([
            'tenant_id' => $tenantId,
            'version' => 1,
            ...$data,
        ]);
    }

    public function updateEligibilityTemplate($templateId, array $data)
    {
        $template = EligibilityTemplate::findOrFail($templateId);
        $template->update($data);

        return $template;
    }

    public function createTemplateVersion($templateId)
    {
        $originalTemplate = EligibilityTemplate::findOrFail($templateId);

        return $originalTemplate->clone();
    }

    public function getActiveTemplate($tenantId)
    {
        return EligibilityTemplate::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->first();
    }

    public function getDefaultTemplate($tenantId)
    {
        return EligibilityTemplate::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->first();
    }

    public function setAsDefault($templateId)
    {
        $template = EligibilityTemplate::findOrFail($templateId);

        EligibilityTemplate::where('tenant_id', $template->tenant_id)
            ->update(['is_default' => false]);

        $template->update(['is_default' => true, 'is_active' => true]);

        return $template;
    }

    public function setAsActive($templateId)
    {
        $template = EligibilityTemplate::findOrFail($templateId);

        EligibilityTemplate::where('tenant_id', $template->tenant_id)
            ->where('id', '!=', $template->id)
            ->update(['is_active' => false]);

        $template->update(['is_active' => true]);

        return $template;
    }

    public function evaluateDonorEligibility($donor, $templateId = null)
    {
        $template = $templateId 
            ? EligibilityTemplate::findOrFail($templateId)
            : $this->getActiveTemplate($donor->tenant_id);

        if (!$template) {
            return [
                'is_eligible' => true,
                'reasons' => ['No eligibility template configured'],
                'template_id' => null,
            ];
        }

        $evaluation = $template->evaluateDonor($donor);

        return [
            'is_eligible' => $evaluation['is_eligible'],
            'reasons' => $evaluation['reasons'],
            'template_id' => $template->id,
            'template_version' => $template->version,
        ];
    }

    public function getDeferralPeriod($templateId, $deferralType)
    {
        $template = EligibilityTemplate::findOrFail($templateId);

        return $template->getDeferralPeriod($deferralType);
    }

    public function listEligibilityTemplates($tenantId)
    {
        return EligibilityTemplate::where('tenant_id', $tenantId)
            ->orderBy('version', 'desc')
            ->get();
    }

    public function deleteTemplate($templateId)
    {
        return EligibilityTemplate::destroy($templateId);
    }

    public function createDefaultTemplates($tenantId)
    {
        $templates = [
            [
                'name' => 'Standard Blood Donor Requirements',
                'description' => 'Standard eligibility criteria for blood donors',
                'is_default' => true,
                'is_active' => true,
                'age_range' => ['min' => 18, 'max' => 65],
                'weight_limit' => 50,
                'hemoglobin_levels' => ['min_male' => 13.5, 'min_female' => 12.5],
                'blood_pressure' => ['systolic_max' => 180, 'diastolic_max' => 100],
                'deferral_periods' => [
                    'cold_or_fever' => 14,
                    'surgery' => 30,
                    'vaccination' => 14,
                    'pregnancy' => 180,
                    'blood_transfusion' => 120,
                ],
                'medications_to_defer' => [
                    'Aspirin',
                    'Warfarin',
                    'Isotretinoin',
                ],
                'conditions_to_defer' => [
                    'HIV',
                    'Hepatitis',
                    'Syphilis',
                    'Malaria',
                ],
            ],
            [
                'name' => 'Strict Medical Requirements',
                'description' => 'More stringent eligibility criteria',
                'is_default' => false,
                'is_active' => false,
                'age_range' => ['min' => 18, 'max' => 55],
                'weight_limit' => 60,
                'hemoglobin_levels' => ['min_male' => 14.0, 'min_female' => 13.0],
                'blood_pressure' => ['systolic_max' => 140, 'diastolic_max' => 90],
                'deferral_periods' => [
                    'cold_or_fever' => 21,
                    'surgery' => 45,
                    'vaccination' => 21,
                    'pregnancy' => 365,
                    'blood_transfusion' => 180,
                ],
                'medications_to_defer' => [
                    'Aspirin',
                    'Warfarin',
                    'Isotretinoin',
                    'Antibiotics',
                ],
                'conditions_to_defer' => [
                    'HIV',
                    'Hepatitis',
                    'Syphilis',
                    'Malaria',
                    'Diabetes',
                ],
            ],
        ];

        $created = [];

        foreach ($templates as $template) {
            $created[] = $this->createEligibilityTemplate($tenantId, $template);
        }

        return $created;
    }
}
