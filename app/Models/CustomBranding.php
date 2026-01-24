<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomBranding extends Model
{
    use \App\Traits\BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'logo_path',
        'favicon_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'text_color',
        'background_color',
        'font_family',
        'custom_css',
        'custom_html_header',
        'custom_html_footer',
        'company_name',
        'company_description',
        'support_email',
        'support_phone',
        'privacy_policy_url',
        'terms_of_service_url',
        'hide_saas_branding',
        'show_powered_by',
        'email_templates',
        'report_branding',
    ];

    protected $casts = [
        'hide_saas_branding' => 'boolean',
        'show_powered_by' => 'boolean',
        'email_templates' => 'json',
        'report_branding' => 'json',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getThemeAssets()
    {
        return [
            'colors' => [
                'primary' => $this->primary_color,
                'secondary' => $this->secondary_color,
                'accent' => $this->accent_color,
                'text' => $this->text_color,
                'background' => $this->background_color,
            ],
            'fonts' => [
                'family' => $this->font_family,
            ],
            'logos' => [
                'main' => $this->logo_path,
                'favicon' => $this->favicon_path,
            ],
        ];
    }

    public function generateThemeCss()
    {
        $css = ":root {\n";
        $css .= "  --primary-color: {$this->primary_color};\n";
        $css .= "  --secondary-color: {$this->secondary_color};\n";
        $css .= "  --accent-color: {$this->accent_color};\n";
        $css .= "  --text-color: {$this->text_color};\n";
        $css .= "  --background-color: {$this->background_color};\n";
        $css .= "  --font-family: {$this->font_family};\n";
        $css .= "}\n";

        if ($this->custom_css) {
            $css .= $this->custom_css;
        }

        return $css;
    }

    public function getEmailTemplate($templateName, $data = [])
    {
        if (!$this->email_templates || !isset($this->email_templates[$templateName])) {
            return null;
        }

        $template = $this->email_templates[$templateName];
        
        foreach ($data as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }

        return $template;
    }
}
