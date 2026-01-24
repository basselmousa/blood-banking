<?php

namespace App\Services;

use App\Models\CustomBranding;
use App\Models\CustomDomain;
use Illuminate\Support\Facades\Storage;

class BrandingService
{
    public function getOrCreateBranding($tenantId)
    {
        return CustomBranding::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'primary_color' => '#FF6B6B',
                'secondary_color' => '#4ECDC4',
                'accent_color' => '#45B7D1',
                'text_color' => '#2C3E50',
                'background_color' => '#FFFFFF',
                'font_family' => 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif',
                'hide_saas_branding' => false,
                'show_powered_by' => true,
            ]
        );
    }

    public function updateBranding($tenantId, array $data)
    {
        $branding = CustomBranding::where('tenant_id', $tenantId)->first();

        if (!$branding) {
            $branding = $this->getOrCreateBranding($tenantId);
        }

        $branding->update($data);

        return $branding;
    }

    public function uploadLogo($tenantId, $file)
    {
        $path = Storage::disk('public')->putFile("tenants/{$tenantId}/branding", $file);

        $branding = CustomBranding::where('tenant_id', $tenantId)->first();
        if ($branding) {
            $branding->update(['logo_path' => Storage::url($path)]);
        }

        return $path;
    }

    public function uploadFavicon($tenantId, $file)
    {
        $path = Storage::disk('public')->putFile("tenants/{$tenantId}/branding", $file);

        $branding = CustomBranding::where('tenant_id', $tenantId)->first();
        if ($branding) {
            $branding->update(['favicon_path' => Storage::url($path)]);
        }

        return $path;
    }

    public function generateThemeCss($tenantId)
    {
        $branding = CustomBranding::where('tenant_id', $tenantId)->first();

        if (!$branding) {
            return $this->getDefaultThemeCss();
        }

        return $branding->generateThemeCss();
    }

    public function getDefaultThemeCss()
    {
        return ":root {\n"
            . "  --primary-color: #FF6B6B;\n"
            . "  --secondary-color: #4ECDC4;\n"
            . "  --accent-color: #45B7D1;\n"
            . "  --text-color: #2C3E50;\n"
            . "  --background-color: #FFFFFF;\n"
            . "  --font-family: Segoe UI, Tahoma, Geneva, Verdana, sans-serif;\n"
            . "}\n";
    }

    public function getThemeAssets($tenantId)
    {
        $branding = CustomBranding::where('tenant_id', $tenantId)->first();

        if (!$branding) {
            return [
                'colors' => [
                    'primary' => '#FF6B6B',
                    'secondary' => '#4ECDC4',
                    'accent' => '#45B7D1',
                    'text' => '#2C3E50',
                    'background' => '#FFFFFF',
                ],
                'fonts' => ['family' => 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif'],
                'logos' => ['main' => null, 'favicon' => null],
            ];
        }

        return $branding->getThemeAssets();
    }

    public function setEmailTemplate($tenantId, $templateName, $templateContent)
    {
        $branding = CustomBranding::where('tenant_id', $tenantId)->first();

        if (!$branding) {
            $branding = $this->getOrCreateBranding($tenantId);
        }

        $templates = $branding->email_templates ?? [];
        $templates[$templateName] = $templateContent;

        $branding->update(['email_templates' => $templates]);

        return $branding;
    }

    public function getEmailTemplate($tenantId, $templateName, $data = [])
    {
        $branding = CustomBranding::where('tenant_id', $tenantId)->first();

        if (!$branding) {
            return null;
        }

        return $branding->getEmailTemplate($templateName, $data);
    }

    public function hideAllSaasBranding($tenantId)
    {
        return $this->updateBranding($tenantId, [
            'hide_saas_branding' => true,
            'show_powered_by' => false,
        ]);
    }

    public function getBrandingForDomain($domain)
    {
        $customDomain = CustomDomain::where('domain', $domain)
            ->where('is_active', true)
            ->where('is_verified', true)
            ->first();

        if (!$customDomain) {
            return null;
        }

        return CustomBranding::where('tenant_id', $customDomain->tenant_id)->first();
    }
}
