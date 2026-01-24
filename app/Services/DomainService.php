<?php

namespace App\Services;

use App\Models\CustomDomain;
use Illuminate\Support\Str;

class DomainService
{
    public function createDomain($tenantId, $domain)
    {
        $baseDomain = $this->extractBaseDomain($domain);
        $verificationToken = Str::random(32);

        return CustomDomain::create([
            'tenant_id' => $tenantId,
            'domain' => $domain,
            'base_domain' => $baseDomain,
            'verification_token' => $verificationToken,
            'is_primary' => false,
            'is_verified' => false,
            'is_active' => false,
        ]);
    }

    public function setPrimaryDomain($customDomainId)
    {
        $customDomain = CustomDomain::findOrFail($customDomainId);

        CustomDomain::where('tenant_id', $customDomain->tenant_id)
            ->update(['is_primary' => false]);

        $customDomain->update(['is_primary' => true, 'is_active' => true]);

        return $customDomain;
    }

    public function verifyDomain($domainId, $verificationToken)
    {
        $domain = CustomDomain::findOrFail($domainId);

        if ($domain->verification_token !== $verificationToken) {
            throw new \Exception('Invalid verification token');
        }

        $domain->verify($verificationToken);

        return $domain;
    }

    public function getVerificationDnsRecords($domainId)
    {
        $domain = CustomDomain::findOrFail($domainId);

        return $domain->getVerificationDnsRecords();
    }

    public function generateSslCertificate($customDomainId)
    {
        $customDomain = CustomDomain::findOrFail($customDomainId);

        // In production, you would integrate with Let's Encrypt or similar
        // For now, we'll just simulate the process
        $sslData = [
            'certificate' => "-----BEGIN CERTIFICATE-----\n" . base64_encode($customDomain->domain) . "\n-----END CERTIFICATE-----",
            'key' => "-----BEGIN PRIVATE KEY-----\n" . base64_encode(Str::random(64)) . "\n-----END PRIVATE KEY-----",
            'expires_at' => now()->addYears(1),
        ];

        $customDomain->update([
            'ssl_certificate' => $sslData['certificate'],
            'ssl_key' => $sslData['key'],
            'ssl_expires_at' => $sslData['expires_at'],
        ]);

        return $customDomain;
    }

    public function activateDomain($customDomainId)
    {
        $customDomain = CustomDomain::findOrFail($customDomainId);

        if (!$customDomain->is_verified) {
            throw new \Exception('Domain must be verified before activation');
        }

        $customDomain->update(['is_active' => true]);

        return $customDomain;
    }

    public function deactivateDomain($customDomainId)
    {
        $customDomain = CustomDomain::findOrFail($customDomainId);

        $customDomain->update(['is_active' => false]);

        return $customDomain;
    }

    public function deleteDomain($customDomainId)
    {
        return CustomDomain::destroy($customDomainId);
    }

    public function listDomainsForTenant($tenantId)
    {
        return CustomDomain::where('tenant_id', $tenantId)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPrimaryDomain($tenantId)
    {
        return CustomDomain::where('tenant_id', $tenantId)
            ->where('is_primary', true)
            ->first();
    }

    public function getActiveDomains($tenantId)
    {
        return CustomDomain::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();
    }

    public function checkSslExpiration($customDomainId, $daysThreshold = 30)
    {
        $customDomain = CustomDomain::findOrFail($customDomainId);

        if ($customDomain->isSslExpiringSoon($daysThreshold)) {
            return [
                'expiring_soon' => true,
                'expires_at' => $customDomain->ssl_expires_at,
                'days_remaining' => $customDomain->ssl_expires_at
                    ? now()->diffInDays($customDomain->ssl_expires_at)
                    : null,
            ];
        }

        return [
            'expiring_soon' => false,
            'expires_at' => $customDomain->ssl_expires_at,
            'days_remaining' => $customDomain->ssl_expires_at
                ? now()->diffInDays($customDomain->ssl_expires_at)
                : null,
        ];
    }

    public function renewSslCertificate($customDomainId)
    {
        return $this->generateSslCertificate($customDomainId);
    }

    private function extractBaseDomain($domain)
    {
        // Extract base domain (e.g., example.com from www.example.com)
        $parts = explode('.', $domain);

        if (count($parts) >= 2) {
            return implode('.', array_slice($parts, -2));
        }

        return $domain;
    }

    public function validateDomainFormat($domain)
    {
        if (!filter_var("http://{$domain}", FILTER_VALIDATE_URL)) {
            return [
                'valid' => false,
                'error' => 'Invalid domain format',
            ];
        }

        if (strlen($domain) > 253) {
            return [
                'valid' => false,
                'error' => 'Domain name too long',
            ];
        }

        return ['valid' => true];
    }

    public function isDomainAvailable($domain)
    {
        $existing = CustomDomain::where('domain', $domain)->exists();

        return !$existing;
    }
}
