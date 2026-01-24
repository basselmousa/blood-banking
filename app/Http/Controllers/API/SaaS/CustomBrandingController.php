<?php

namespace App\Http\Controllers\API\SaaS;

use App\Http\Controllers\Controller;
use App\Models\CustomBranding;
use App\Services\BrandingService;
use Illuminate\Http\Request;

class CustomBrandingController extends Controller
{
    protected $brandingService;

    public function __construct(BrandingService $brandingService)
    {
        $this->brandingService = $brandingService;
        $this->middleware('auth:sanctum');
    }

    public function show()
    {
        $tenantId = auth()->user()->current_tenant_id;

        $branding = $this->brandingService->getOrCreateBranding($tenantId);

        return response()->json([
            'success' => true,
            'data' => $branding,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_family' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_description' => 'nullable|string|max:1000',
            'support_email' => 'nullable|email',
            'support_phone' => 'nullable|string|max:20',
            'custom_css' => 'nullable|string',
            'custom_html_header' => 'nullable|string',
            'custom_html_footer' => 'nullable|string',
            'hide_saas_branding' => 'nullable|boolean',
            'show_powered_by' => 'nullable|boolean',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $branding = $this->brandingService->updateBranding($tenantId, $request->all());

        return response()->json([
            'success' => true,
            'data' => $branding,
            'message' => 'Branding updated successfully',
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,gif,svg|max:2048',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $path = $this->brandingService->uploadLogo($tenantId, $request->file('logo'));

        return response()->json([
            'success' => true,
            'data' => ['path' => $path],
            'message' => 'Logo uploaded successfully',
        ]);
    }

    public function uploadFavicon(Request $request)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:jpeg,png,gif,ico|max:512',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $path = $this->brandingService->uploadFavicon($tenantId, $request->file('favicon'));

        return response()->json([
            'success' => true,
            'data' => ['path' => $path],
            'message' => 'Favicon uploaded successfully',
        ]);
    }

    public function getThemeCss()
    {
        $tenantId = auth()->user()->current_tenant_id;

        $css = $this->brandingService->generateThemeCss($tenantId);

        return response($css, 200, ['Content-Type' => 'text/css']);
    }

    public function getThemeAssets()
    {
        $tenantId = auth()->user()->current_tenant_id;

        $assets = $this->brandingService->getThemeAssets($tenantId);

        return response()->json([
            'success' => true,
            'data' => $assets,
        ]);
    }

    public function setEmailTemplate(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string',
            'template_content' => 'required|string',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $branding = $this->brandingService->setEmailTemplate(
            $tenantId,
            $request->template_name,
            $request->template_content
        );

        return response()->json([
            'success' => true,
            'data' => $branding,
            'message' => 'Email template saved successfully',
        ]);
    }

    public function getEmailTemplate(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string',
        ]);

        $tenantId = auth()->user()->current_tenant_id;

        $template = $this->brandingService->getEmailTemplate(
            $tenantId,
            $request->template_name
        );

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['template' => $template],
        ]);
    }

    public function hideAllBranding()
    {
        $tenantId = auth()->user()->current_tenant_id;

        $branding = $this->brandingService->hideAllSaasBranding($tenantId);

        return response()->json([
            'success' => true,
            'data' => $branding,
            'message' => 'SaaS branding hidden successfully',
        ]);
    }

    public function getPreview()
    {
        $tenantId = auth()->user()->current_tenant_id;

        $branding = $this->brandingService->getOrCreateBranding($tenantId);
        $assets = $this->brandingService->getThemeAssets($tenantId);
        $css = $this->brandingService->generateThemeCss($tenantId);

        return response()->json([
            'success' => true,
            'data' => [
                'branding' => $branding,
                'assets' => $assets,
                'css' => $css,
            ],
        ]);
    }
}
