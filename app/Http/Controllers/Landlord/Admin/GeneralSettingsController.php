<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Enums\AdminGetRoutesEnum;
use App\Helpers\FlashMsg;
use App\Helpers\ResponseMessage;
use App\Http\Controllers\Controller;
use App\Jobs\SyncLocalFileWithCloud;
use App\Jobs\SyncTenantLocalFileWithCloud;
use App\Mail\BasicMail;
use App\Models\MediaUploader;
use App\Models\Page;
use App\Models\PaymentGateway;
use App\Models\Tenant;
use Config;
use Database\Seeders\Tenant\AddNewTenantPaymentGateway;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Helpers\SanitizeInput;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Facades\Tenancy;
use Xgenious\XgApiClient\Facades\XgApiClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GeneralSettingsController extends Controller
{
    const BASE_PATH = 'landlord.admin.general-settings.';

    public function __construct()
    {
        $this->middleware('permission:general-settings-site-identity', ['only' => ['site_identity', 'update_site_identity']]);
        $this->middleware('permission:general-settings-page-settings', ['only' => ['page_settings', 'update_page_settings']]);
        $this->middleware('permission:general-settings-global-navbar-settings', ['only' => ['global_variant_navbar', 'update_global_variant_navbar']]);
        $this->middleware('permission:general-settings-global-footer-settings', ['only' => ['global_variant_footer', 'update_global_variant_footer']]);
        $this->middleware('permission:general-settings-basic-settings', ['only' => ['basic_settings', 'update_basic_settings']]);
        $this->middleware('permission:general-settings-color-settings', ['only' => ['color_settings', 'update_color_settings']]);
        $this->middleware('permission:general-settings-typography-settings', ['only' => ['typography_settings', 'get_single_font_variant', 'update_typography_settings']]);
        $this->middleware('permission:general-settings-seo-settings', ['only' => ['seo_settings', 'update_seo_settings']]);
        $this->middleware('permission:general-settings-third-party-scripts', ['only' => ['update_scripts_settings', 'scripts_settings']]);
        $this->middleware('permission:general-settings-smtp-settings', ['only' => ['email_settings', 'update_email_settings']]);
        $this->middleware('permission:general-settings-payment-settings', ['only' => ['payment_settings', 'update_payment_settings']]);
        $this->middleware('permission:general-settings-custom-css-settings', ['only' => ['custom_css_settings', 'update_custom_css_settings']]);
        $this->middleware('permission:general-settings-custom-js-settings', ['only' => ['custom_js_settings', 'update_custom_js_settings']]);
        $this->middleware('permission:general-settings-licence-settings', ['only' => ['license_settings', 'update_license_settings']]);
        $this->middleware('permission:general-settings-cache-clear-settings', ['only' => ['cache_settings', 'update_cache_settings']]);
    }


    public function page_settings()
    {
        $all_home_pages = Page::where(['status' => 1])->get();
        return view(self::BASE_PATH . 'page-settings', compact('all_home_pages'));
    }

    public function update_page_settings(Request $request)
    {
        $this->validate($request, [
            'home_page' => 'nullable|string',
            'pricing_plan' => 'nullable|string',
            'blog_page' => 'nullable|string',
            'shop_page' => 'nullable|string',
            'digital_shop_page' => 'nullable|string',
            'track_order' => 'nullable|string',
            'terms_condition' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
        ]);
        $fields = [
            'home_page', 'pricing_plan', 'blog_page', 'shop_page', 'digital_shop_page', 'track_order', 'terms_condition', 'privacy_policy'
        ];

        foreach ($fields as $field) {
            update_static_option($field, $request->$field);
        }
        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function update_page_settings_home(Request $request)
    {
        $validated_data = $request->validate([
            'home_page' => 'required|integer'
        ]);
        abort_if($request->method() == 'GET', 404);

        update_static_option('home_page', $validated_data['home_page']);

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function basic_settings()
    {
        return view(self::BASE_PATH . 'basic-settings');
    }

    public function update_basic_settings(Request $request)
    {
        $nonlang_fields = [
            'dark_mode_for_admin_panel' => 'nullable|string',
            'maintenance_mode' => 'nullable|string',
            'backend_preloader' => 'nullable|string',
            'user_email_verify_status' => 'nullable|string',
            'language_selector_status' => 'nullable|string',
            'guest_order_system_status' => 'nullable|string',
            'timezone' => 'nullable',
            'placeholder_image' => 'nullable|integer'
        ];

        $request->validate($nonlang_fields);
        $fields = [
            'site_title' => 'nullable|string',
            'site_tag_line' => 'nullable|string',
            'site_footer_copyright_text' => 'nullable|string',
        ];

        $request->validate($fields);
        foreach ($fields as $field_name => $rules) {
            update_static_option($field_name, SanitizeInput::esc_html($request->$field_name));
        }

        foreach ($nonlang_fields as $field_name => $rules) {
            update_static_option($field_name, $request->$field_name);
        }

        if (!\tenant()) {
            $timezone = get_static_option('timezone');
            if (!empty($timezone)) {
                setEnvValue(['APP_TIMEZONE' => $timezone]);
            }

            if (!empty($request->debug_mode)) {
                setEnvValue(['APP_DEBUG' => 'true']);
            } else {
                setEnvValue(['APP_DEBUG' => 'false']);
            }
        }

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function site_identity()
    {
        return view(self::BASE_PATH . 'site-identity');
    }

    public function update_site_identity(Request $request)
    {
        $fields = [
            'site_logo' => 'required|integer',
            'site_white_logo' => 'required|integer',
            'site_favicon' => 'required|integer',
        ];
        
      $validated =  $request->validate($fields);
        
        foreach (['site_logo', 'site_white_logo'] as $field) {
            if ($request->has($field)) {
                $this->validateLogoSize($request->{$field});
            }
        }


        foreach ($fields as $field_name => $rules) {
            update_static_option($field_name, $request->$field_name);
        }
        
        
        return response()->success(ResponseMessage::SettingsSaved());
    }



        function validateLogoSize($imageId) {
            // Get the image details
            $logo = get_attachment_image_by_id($imageId);
        
            // Fetch the image URL
            $imageUrl = $logo['img_url'];
        
            // Use cURL to download the image to a temporary file
            $tempImage = tempnam(sys_get_temp_dir(), 'logo_');
            $ch = curl_init($imageUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $imageData = curl_exec($ch);
            curl_close($ch);
        
            if ($imageData === false) {
                throw ValidationException::withMessages([
                    'site_logo' => 'Failed to fetch the image.',
                ]);
            }
        
            // Save the image data to the temporary file
            file_put_contents($tempImage, $imageData);
        
            // Get the image size from the temporary file
            $logoInfo = getimagesize($tempImage);
        
            if (is_array($logoInfo)) {
                $width = $logoInfo[0];
                $height = $logoInfo[1];
        
                // Check if the width is 300px and the height is 80px
                if ($width !== 300 || $height !== 80) {
                    unlink($tempImage);  // Remove the temporary file after use
                    throw ValidationException::withMessages([
                        'site_logo' => 'The logo should be exactly 300x80 pixels.',
                    ]);
                }
            }
        
            // Optionally, remove the temporary file after use
            unlink($tempImage);
        }





    public function email_settings()
    {
        return view(self::BASE_PATH . 'tenant-email-settings');
    }

    public function update_email_settings(Request $request)
    {
        $fields = $request->validate([
            'site_global_email' => 'required|email',
            'site_smtp_host' => 'required|string|regex:/^\S*$/u',
            'site_smtp_username' => 'required|string',
            'site_smtp_password' => 'required|string',
            'site_smtp_port' => 'required|numeric',
            'site_smtp_encryption' => 'required|string',
            'site_smtp_driver' => 'required|string',
        ]);

        foreach ($fields as $field_name => $rule) {
            update_static_option($field_name, $rule);
        }

        if (is_null(\tenant())) {
            update_static_option('site_global_email', $request->site_global_email);

            setEnvValue([
                'MAIL_MAILER' => $request->site_smtp_driver,
                'MAIL_HOST' => $request->site_smtp_host,
                'MAIL_PORT' => $request->site_smtp_port,
                'MAIL_USERNAME' => $request->site_smtp_username,
                'MAIL_PASSWORD' => addQuotes($request->site_smtp_password),
                'MAIL_ENCRYPTION' => $request->site_smtp_encryption,
                'MAIL_FROM_ADDRESS' => $request->site_global_email
            ]);
        }

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function color_settings()
    {
        return view(self::BASE_PATH . 'color-settings');
    }

    public function update_color_settings(Request $request)
    {
        $theme_slug = getSelectedThemeSlug();

        if (tenant()) {
            $fields = [
                'main_color_one_' . $theme_slug => 'nullable|string|max:191',
                'main_color_two_' . $theme_slug => 'nullable|string|max:191',
                'main_color_three_' . $theme_slug => 'nullable|string|max:191',
                'main_color_four_' . $theme_slug => 'nullable|string|max:191',
                'secondary_color_' . $theme_slug => 'nullable|string|max:191',
                'secondary_color_two_' . $theme_slug => 'nullable|string|max:191',
                'section_bg_1_' . $theme_slug => 'nullable|string|max:191',
                'section_bg_2_' . $theme_slug => 'nullable|string|max:191',
                'section_bg_3_' . $theme_slug => 'nullable|string|max:191',
                'section_bg_4_' . $theme_slug => 'nullable|string|max:191',
                'section_bg_5_' . $theme_slug => 'nullable|string|max:191',
                'section_bg_6_' . $theme_slug => 'nullable|string|max:191',
                'breadcrumb_bg_' . $theme_slug => 'nullable|string|max:191',
                'heading_color_' . $theme_slug => 'nullable|string|max:191',
                'body_color_' . $theme_slug => 'nullable|string|max:191',
                'light_color_' . $theme_slug => 'nullable|string|max:191',
                'extra_light_color_' . $theme_slug => 'nullable|string|max:191',
                'review_color_' . $theme_slug => 'nullable|string|max:191',
                'feedback_bg_item_' . $theme_slug => 'nullable|string|max:191',
                'new_color_' . $theme_slug => 'nullable|string|max:191',
            ];
        } else {
            $fields = [
                'main_color_one' => 'nullable|string|max:191',
                'main_color_two' => 'nullable|string|max:191',
                'main_color_three' => 'nullable|string|max:191',
                'main_color_four' => 'nullable|string|max:191',
                'secondary_color' => 'nullable|string|max:191',
                'secondary_color_two' => 'nullable|string|max:191',
                'section_bg_1' => 'nullable|string|max:191',
                'section_bg_2' => 'nullable|string|max:191',
                'section_bg_3' => 'nullable|string|max:191',
                'section_bg_4' => 'nullable|string|max:191',
                'section_bg_5' => 'nullable|string|max:191',
                'section_bg_6' => 'nullable|string|max:191',
                'heading_color' => 'nullable|string|max:191',
                'body_color' => 'nullable|string|max:191',
                'light_color' => 'nullable|string|max:191',
                'extra_light_color' => 'nullable|string|max:191',
                'review_color' => 'nullable|string|max:191',
                'feedback_bg_item' => 'nullable|string|max:191',
                'new_color' => 'nullable|string|max:191',
            ];
        }

        $this->validate($request, $fields);

        foreach ($fields as $field_name => $rules) {
            update_static_option($field_name, $request->$field_name);
        }

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function typography_settings()
    {
        $prefix = is_null(tenant()) ? 'landlord' : 'tenant';
        $all_google_fonts = file_get_contents('assets/' . $prefix . '/frontend/webfonts/google-fonts.json');

        return view(self::BASE_PATH . 'typography-settings')->with(['google_fonts' => json_decode($all_google_fonts)]);
    }

    public function get_single_font_variant(Request $request)
    {
        $prefix = is_null(tenant()) ? 'landlord' : 'tenant';
        $all_google_fonts = file_get_contents('assets/' . $prefix . '/frontend/webfonts/google-fonts.json');
        $decoded_fonts = json_decode($all_google_fonts, true);

        $data = [
            'decoded_fonts' => $decoded_fonts[$request->font_family],
            'theme' => $request->theme
        ];

        return response()->json($data);
    }

    public function update_typography_settings(Request $request)
    {
        $suffix = getSelectedThemeSlug();

        if (tenant()) {
            $fields = [
                'body_font_family_' . $suffix => 'required|string|max:191',
                'body_font_variant_' . $suffix => 'required',
                'heading_font_' . $suffix => 'nullable|string',
                'heading_font_family_' . $suffix => 'nullable|string|max:191',
                'heading_font_variant_' . $suffix => 'nullable',
            ];

            $save_data = [
                'body_font_family_' . $suffix,
                'heading_font_family_' . $suffix,
                'heading_font_' . $suffix
            ];

            $font_variant = [
                'body_font_variant_' . $suffix,
                'heading_font_variant_' . $suffix,
            ];

            $this->validate($request, $fields);

            foreach ($save_data as $item) {
                update_static_option($item, $request->$item);
            }

            // Issue to fix
            foreach ($font_variant as $variant) {
                update_static_option($variant, serialize(!empty($request->$variant) ? $request->$variant : ['regular']));
            }
        } else {
            $fields = [
                'body_font_family' => 'required|string|max:191',
                'heading_font' => 'nullable|string',
                'heading_font_family' => 'nullable|string|max:191',
            ];

            $this->validate($request, $fields);
            foreach ($fields as $key => $item) {
                update_static_option($key, $request->$key);
            }

            $font_variant = [
                'body_font_variant' => 'required',
                'heading_font_variant' => 'nullable'
            ];

            $this->validate($request, $font_variant);
            foreach ($font_variant as $key => $item) {
                update_static_option($key, serialize($request->$key));
            }
        }

        return redirect()->back()->with(['msg' => __('Typography Settings Updated..'), 'type' => 'success']);
    }


    public function seo_settings()
    {
        return view(self::BASE_PATH . 'seo-settings');
    }

    public function update_seo_settings(Request $request)
    {
        $fields = [
            'site_meta_author' => 'nullable|string',
            'site_meta_keywords' => 'nullable|string',
            'site_meta_description' => 'nullable|string',
            'site_og_meta_title' => 'nullable|string',
            'site_og_meta_description' => 'nullable|string',
            'site_og_meta_image' => 'nullable|string',
            'site_fb_meta_title' => 'nullable|string',
            'site_fb_meta_description' => 'nullable|string',
            'site_fb_meta_image' => 'nullable|string',
            'site_tw_meta_title' => 'nullable|string',
            'site_tw_meta_description' => 'nullable|string',
            'site_tw_meta_image' => 'nullable|string',
        ];

        $this->validate($request, $fields);
        foreach ($fields as $field_name => $rules) {
            update_static_option($field_name, SanitizeInput::esc_html($request->$field_name));
        }

        return response()->success(ResponseMessage::SettingsSaved());
    }


    public function smtp_settings()
    {
        return view(self::BASE_PATH . 'smtp-settings');
    }

    public function update_smtp_settings(Request $request)
    {
        $fields = [
            'site_global_email' => 'required|email',
            'site_smtp_host' => 'required|string|regex:/^\S*$/u',
            'site_smtp_username' => 'required|string',
            'site_smtp_password' => 'required|string',
            'site_smtp_port' => 'required|numeric',
            'site_smtp_encryption' => 'required|string',
            'site_smtp_driver' => 'required|string',
        ];

        $this->validate($request, $fields);
        foreach ($fields as $field_name => $rules) {
            update_static_option_central($field_name, $request->$field_name);
            update_static_option($field_name, $request->$field_name);
        }

        setEnvValue([
            'MAIL_MAILER' => $request->site_smtp_driver,
            'MAIL_HOST' => $request->site_smtp_host,
            'MAIL_PORT' => $request->site_smtp_port,
            'MAIL_USERNAME' => $request->site_smtp_username,
            'MAIL_PASSWORD' => addQuotes($request->site_smtp_password),
            'MAIL_ENCRYPTION' => $request->site_smtp_encryption,
            'MAIL_FROM_ADDRESS' => $request->site_global_email
        ]);

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function ssl_settings()
    {
        return view(self::BASE_PATH . 'ssl-settings');
    }

    public function update_ssl_settings(Request $request)
    {
        $request->validate([
            'site_force_ssl_redirection' => 'nullable'
        ]);

        update_static_option('site_force_ssl_redirection', $request->site_force_ssl_redirection);

        return response()->success(ResponseMessage::SettingsSaved());
    }

    public function send_test_mail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email'
        ]);

        try {
            $message = __('Hi') . (\tenant() ? ' Tenant' : ' Landlord') . ',<br>';
            $message .= __('This is Test Mail');

            Mail::to($request->email)->send(new BasicMail($message, __('SMTP Test email')));
        } catch (\Exception $e) {
            if ($e->getCode() == 535) {
                return response()->warning(__('We could not connect with your mail server or the email limit is reached.'));
            }
            return response()->warning($e->getMessage());
        }

        return response()->success(ResponseMessage::mailSendSuccess());
    }


    public function cache_settings()
    {
        return view(self::BASE_PATH . 'cache-settings');
    }

    public function update_cache_settings(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|string'
        ]);
        switch ($request->type) {
            case "route":
            case "view":
            case "config":
            case "event":
//            case "queue":
                Artisan::call($request->type . ':clear');
                break;
            default:
                Artisan::call('optimize:clear');
                break;
        }
        return response()->success(ResponseMessage::success(sprintf(__('%s Cache Cleared'), ucfirst($request->type))));
    }

    public function third_party_script_settings()
    {
        return view(self::BASE_PATH . 'third-party');
    }


    public function update_third_party_script_settings(Request $request)
    {
        $this->validate($request, [
            'instagram_access_token' => 'nullable|string',
            'tawk_api_key' => 'nullable|string',
            'google_adsense_id' => 'nullable|string',
            'site_third_party_tracking_code' => 'nullable|string',
            'site_google_analytics' => 'nullable|string',
            'site_google_captcha_v3_secret_key' => 'nullable|string',
            'site_google_captcha_v3_site_key' => 'nullable|string',
        ]);

        update_static_option('site_disqus_key', $request->site_disqus_key);

        $fields = [
            'site_google_captcha_v3_secret_key',
            'site_google_captcha_v3_site_key',
            'site_third_party_tracking_code',
            'site_google_analytics',
            'tawk_api_key',
            'instagram_access_token'
        ];
        foreach ($fields as $field) {
            update_static_option($field, $request->$field);
        }

        return redirect()->back()->with(['msg' => __('Third Party Scripts Settings Updated..'), 'type' => 'success']);
    }

    public function custom_css_settings()
    {
        $prefix = is_null(tenant()) ? 'landlord' : 'tenant';
        $id = is_null(tenant()) ? '' : tenant()->id . '/';

        $custom_css = '/* Write Custom Css Here */';
        if (file_exists('assets/' . $prefix . '/frontend/css/' . $id . 'dynamic-style.css')) {
            $custom_css = file_get_contents('assets/' . $prefix . '/frontend/css/' . $id . 'dynamic-style.css');
        } else {
            $directory_name = 'assets/' . $prefix . '/frontend/css/' . $id;

            if (!is_null(tenant())) {
                mkdir($directory_name, 0777, true);
            }
            fopen($directory_name . 'dynamic-style.css', 'w+');
        }

        return view(self::BASE_PATH . 'custom-css')->with(['custom_css' => $custom_css]);
    }

    public function update_custom_css_settings(Request $request)
    {
        $prefix = is_null(tenant()) ? 'landlord' : 'tenant';
        $id = is_null(tenant()) ? '' : tenant()->id . '/';
        file_put_contents('assets/' . $prefix . '/frontend/css/' . $id . 'dynamic-style.css', $request->custom_css_area);

        return redirect()->back()->with(['msg' => __('Custom Style Successfully Added...'), 'type' => 'success']);
    }

    public function custom_js_settings()
    {
        $custom_js = '/* Write Custom js Here */';
        $prefix = is_null(tenant()) ? 'landlord' : 'tenant';
        $id = is_null(tenant()) ? '' : tenant()->id . '/';

        if (file_exists('assets/' . $prefix . '/frontend/js/' . $id . 'dynamic-script.js')) {
            $custom_js = file_get_contents('assets/' . $prefix . '/frontend/js/' . $id . 'dynamic-script.js');
        } else {
            $directory_name = 'assets/' . $prefix . '/frontend/js/' . $id;

            if (!is_null(tenant())) {
                mkdir($directory_name, 0777, true);
            }
            fopen($directory_name . 'dynamic-script.js', 'w+');
        }

        return view(self::BASE_PATH . 'custom-js')->with(['custom_js' => $custom_js]);
    }

    public function update_custom_js_settings(Request $request)
    {
        $prefix = is_null(tenant()) ? 'landlord' : 'tenant';
        $id = is_null(tenant()) ? '' : tenant()->id . '/';

        file_put_contents('assets/' . $prefix . '/frontend/js/' . $id . 'dynamic-script.js', $request->custom_js_area);
        return redirect()->back()->with(['msg' => __('Custom Script Successfully Added...'), 'type' => 'success']);
    }

    public function payment_settings()
    {
        $all_gateway = PaymentGateway::all();
        return view(self::BASE_PATH . 'payment-gateway', compact('all_gateway'));
    }


    public function update_payment_settings(Request $request)
    {
        $this->validate($request, [
            'site_global_currency' => 'nullable|string|max:191',
            'site_currency_symbol_position' => 'nullable|string|max:191',
            'site_default_payment_gateway' => 'nullable|string|max:191',
        ]);

        $save_data = [
            'site_global_currency',
            'site_global_payment_gateway',
            'site_currency_symbol_position',
            'site_default_payment_gateway',
            'currency_amount_type_status',
            'site_custom_currency_symbol',
            'site_custom_currency_thousand_separator',
            'site_custom_currency_decimal_separator',
            'cash_on_delivery'
        ];

        foreach ($save_data as $item) {
            update_static_option($item, $request->$item);
        }

        $global_currency = get_static_option('site_global_currency');

        $this->validate($request, [
            'site_usd_to_ngn_exchange_rate' => 'nullable|numeric',
            'site_euro_to_ngn_exchange_rate' => 'nullable|numeric',
            'site_' . strtolower($global_currency) . '_to_usd_exchange_rate' => 'nullable|numeric',
            'site_' . strtolower($global_currency) . '_to_idr_exchange_rate' => 'nullable|numeric',
            'site_' . strtolower($global_currency) . '_to_inr_exchange_rate' => 'nullable|numeric',
            'site_' . strtolower($global_currency) . '_to_ngn_exchange_rate' => 'nullable|numeric',
            'site_' . strtolower($global_currency) . '_to_zar_exchange_rate' => 'nullable|numeric',
            'site_' . strtolower($global_currency) . '_to_brl_exchange_rate' => 'nullable|numeric',
        ]);

        $save_data_exchange_rates = [
            'site_usd_to_ngn_exchange_rate',
            'site_euro_to_ngn_exchange_rate',
            'site_' . strtolower($global_currency) . '_to_usd_exchange_rate',
            'site_' . strtolower($global_currency) . '_to_idr_exchange_rate',
            'site_' . strtolower($global_currency) . '_to_inr_exchange_rate',
            'site_' . strtolower($global_currency) . '_to_ngn_exchange_rate',
            'site_' . strtolower($global_currency) . '_to_zar_exchange_rate',
            'site_' . strtolower($global_currency) . '_to_brl_exchange_rate',
        ];

        foreach ($save_data_exchange_rates as $item) {
            update_static_option($item, SanitizeInput::esc_html($request->$item));
        }

        Artisan::call('cache:clear');
        return redirect()->back()->with([
            'msg' => __('Payment Settings Updated..'),
            'type' => 'success'
        ]);
    }

    public function database_upgrade()
    {
        return view(self::BASE_PATH . 'database-upgrade');
    }

    public function update_database_upgrade(Request $request)
    {
        setEnvValue(['APP_ENV' => 'local']);
        Artisan::call('migrate', ['--force' => true]);

        //todo run a query to get all the tenant then run migrate one by one...
        Tenant::latest()->chunk(50, function ($tenants) {
            foreach ($tenants as $tenant) {
                try {
                    Config::set("database.connections.mysql.engine", "InnoDB");
                    Artisan::call('tenants:migrate', ['--force' => true, '--tenants' => $tenant->id]);
                } catch (\Exception $e) {
                    //if issue is related to the mysql database engine,
                }
            }
        });

        Artisan::call('db:seed', ['--force' => true]);

        if (empty(get_static_option('iyzipay_payment_gateway_added_tenant'))) {
            Artisan::call('tenants:seed', ['--class' => AddNewTenantPaymentGateway::class, '--force' => true]);
            update_static_option('iyzipay_payment_gateway_added_tenant', true);
            delete_static_option('theme_modify_seeder_ran');
            delete_static_option('get_script_version_for_seed');
        }

        Artisan::call('cache:clear');
        setEnvValue(['APP_ENV' => 'production']);
        return redirect()->back()->with(['msg' => __('Database Upgraded successfully.'), 'type' => 'success']);
    }

    public function license_settings()
    {
        if (!is_null(tenant())) {
            return redirect()->route('tenant.admin.dashboard');
        }
        return view(self::BASE_PATH . 'license-settings');
    }


    public function update_license_settings(Request $request)
    {
        if (!is_null(tenant())) {
            return redirect()->route('tenant.admin.dashboard');
        }

        $request->validate([
            'site_license_key' => 'required|string|max:191',
            'envato_username' => 'required|string|max:191',
        ]);

        $result = XgApiClient::activeLicense($request->site_license_key, $request->envato_username);
        $type = "danger";
        $msg = __("could not able to verify your license key, please try after sometime, if you still face this issue, contact support");
        if (!empty($result["success"]) && $result["success"]) {
            update_static_option('site_license_key', $request->site_license_key);
            update_static_option('item_license_status', $result['success'] ? 'verified' : "");
            update_static_option('item_license_msg', $result['message']);
            $type = $result['success'] ? 'success' : "danger";
            $msg = $result['message'];
        }

        return redirect()->back()->with(['msg' => $msg, 'type' => $type]);
    }

    public function breadcrumb()
    {
        return view('landlord.admin.appearance-settings.breadcrumb-settings');
    }

    public function breadcrumb_update(Request $request)
    {
        $data = $request->validate([
            'background_left_shape_image' => 'nullable|integer',
            'background_right_shape_image' => 'nullable|integer',
            'background_image_one' => 'nullable|integer',
            'background_image_two' => 'nullable|integer',
            'background_image_three' => 'nullable|integer',
            'background_image_four' => 'nullable|integer',
            'background_image_five' => 'nullable|integer'
        ]);

        foreach ($data as $key => $item) {
            update_static_option($key, $item);
        }

        return back()->with(FlashMsg::update_succeed('breadcrumb'));
    }

    public function highlight()
    {
        return view('landlord.admin.appearance-settings.highlight-settings');
    }

    public function highlight_update(Request $request)
    {
        $data = $request->validate([
            'highlight_text_shape' => 'nullable|integer',
        ]);

        foreach ($data as $key => $item) {
            update_static_option($key, $item);
        }

        return back()->with(FlashMsg::update_succeed('Highlight Text Shape Image'));
    }

    public function gdpr_settings()
    {
        return view(self::BASE_PATH . 'gdpr-settings');
    }

    public function update_gdpr_cookie_settings(Request $request)
    {
        $this->validate($request, [
            'site_gdpr_cookie_enabled' => 'nullable|string|max:191',
            'site_gdpr_cookie_expire' => 'required|string|max:191',
            'site_gdpr_cookie_delay' => 'required|string|max:191',
        ]);

        $this->validate($request, [
            "site_gdpr_cookie_title" => 'nullable|string',
            "site_gdpr_cookie_message" => 'nullable|string',
            "site_gdpr_cookie_more_info_label" => 'nullable|string',
            "site_gdpr_cookie_more_info_link" => 'nullable|string',
            "site_gdpr_cookie_accept_button_label" => 'nullable|string',
            "site_gdpr_cookie_decline_button_label" => 'nullable|string',
        ]);

        $fields = [
            "site_gdpr_cookie_title",
            "site_gdpr_cookie_message",
            "site_gdpr_cookie_more_info_label",
            "site_gdpr_cookie_more_info_link",
            "site_gdpr_cookie_accept_button_label",
            "site_gdpr_cookie_decline_button_label",
            "site_gdpr_cookie_manage_button_label",
            "site_gdpr_cookie_manage_title",
        ];

        foreach ($fields as $field) {
            update_static_option($field, $request->$field);
        }

        $all_fields = [
            'site_gdpr_cookie_manage_item_title',
            'site_gdpr_cookie_manage_item_description',
        ];

        foreach ($all_fields as $field) {
            $value = $request->$field ?? [];
            update_static_option($field, serialize($value));
        }

        update_static_option('site_gdpr_cookie_delay', $request->site_gdpr_cookie_delay);
        update_static_option('site_gdpr_cookie_enabled', $request->site_gdpr_cookie_enabled);
        update_static_option('site_gdpr_cookie_expire', $request->site_gdpr_cookie_expire);

        return redirect()->back()->with(['msg' => __('GDPR Cookie Settings Updated..'), 'type' => 'success']);
    }


    public function software_update_check_settings(Request $request)
    {
        //todo run app update and database migrate here for test...
        return view(self::BASE_PATH . "check-update");
    }

    public function update_version_check(Request $request)
    {
        $result = XgApiClient::checkForUpdate(get_static_option("site_license_key"), get_static_option_central("get_script_version"));

        if (isset($result["success"]) && $result["success"]) {


            $productUid = $result['data']['product_uid'] ?? null;
            $clientVersion = $result['data']['client_version'] ?? null;
            $latestVersion = $result['data']['latest_version'] ?? null;
            $productName = $result['data']['product'] ?? null;
            $releaseDate = $result['data']['release_date'] ?? null;
            $changelog = $result['data']['changelog'] ?? null;
            $phpVersionReq = $result['data']['php_version'] ?? null;
            $mysqlVersionReq = $result['data']['mysql_version'] ?? null;
            $extensions = $result['data']['extension'] ?? null;
            $isTenant = $result['data']['is_tenant'] ?? null;
            $daysDiff = $releaseDate;
            $msg = $result['data']['message'] ?? null;

            $output = "";
            $phpVCompare = version_compare(number_format((float)PHP_VERSION, 1), $phpVersionReq == 8 ? '8.0' : $phpVersionReq, '>=');
            $mysqlServerVersion = DB::select('select version()')[0]->{'version()'};
            $mysqlVCompare = version_compare(number_format((float)$mysqlServerVersion, 1), $mysqlVersionReq, '<=');
            $extensionReq = true;
            if ($extensions) {
                foreach (explode(',', str_replace(' ', '', strtolower($extensions))) as $extension) {
                    if (!empty($extension)) continue;
                    $extensionReq = XgApiClient::extensionCheck($extension);
                }
            }
            if (($phpVCompare === false || $mysqlVCompare === false) && $extensionReq === false) {
                $output .= '<div class="text-danger">' . __('Your server does not have required software version installed.  Required: Php') . $phpVersionReq == 8 ? '8.0' : $phpVersionReq . ', Mysql' . $mysqlVersionReq . '/ Extensions:' . $extensions . 'etc </div>';
                return response()->json(["msg" => $result["message"], "type" => "success", "markup" => $output]);
            }

            if (!empty($latestVersion)) {
                $output .= '<div class="text-success">' . $msg . '</div>';
                $output .= '<div class="card text-center" ><div class="card-header bg-transparent text-warning" >' . __("Please backup your database & script files before upgrading.") . '</div>';
                $output .= '<div class="card-body" ><h5 class="card-title" >' . __("new Version") . ' (' . $latestVersion . ') ' . __("is Available for") . ' ' . $productName . '!</h5 >';
                $updateActionUrl = route('landlord.admin.general.update.download.settings', [$productUid, $isTenant]);
                $output .= '<a href = "#"  class="btn btn-warning" id="update_download_and_run_update" data-version="' . $latestVersion . '" data-action="' . $updateActionUrl . '"> <i class="las la-spinner la-spin d-none"></i>' . __("Download & Update") . ' </a>';
                $output .= '<small class="text-warning d-block">' . __('it can take upto 5-10min to complete update download and initiate upgrade') . '</small></div>';
                $changesLongByLine = explode("\n", $changelog);
                $output .= '<p class="changes-log">';
                $output .= '<strong>' . __("Released:") . " " . $daysDiff . " " . "</strong><br>";
                $output .= "-------------------------------------------<br>";
                foreach ($changesLongByLine as $cg) {
                    $output .= $cg . "<br>";
                }
                $output .= '</p>';

                $output .= '</div>';
            }

            return response()->json(["msg" => $result["message"], "type" => "success", "markup" => $output]);
        }

        return response()->json(["msg" => $result["message"], "type" => "danger", "markup" => "<p class='text-danger'>" . $result["message"] . "</p>"]);

    }

    public function updateDownloadLatestVersion($productUid, $isTenant)
    {
        $version = \request()->get("version");
        //todo wrap this function through xgapiclient facades
        $getItemLicenseKey = get_static_option('site_license_key');
        $return_val = XgApiClient::downloadAndRunUpdateProcess($productUid, $isTenant, $getItemLicenseKey, $version);

        if (is_array($return_val)) {
            return response()->json(['msg' => $return_val['msg'], 'type' => $return_val['type']]);
        } elseif (is_bool($return_val) && $return_val) {
            return response()->json(['msg' => __('system upgrade success'), 'type' => 'success']);
        }
        //it is false
        return response()->json(['msg' => __('Update failed, please contact support for further assistance'), 'type' => 'danger']);
    }

    public function license_key_generate(Request $request)
    {
        $request->validate([
            "envato_purchase_code" => "required",
            "envato_username" => "required",
            "email" => "required",
        ]);
        $res = XgApiClient::VerifyLicense(purchaseCode: $request->envato_purchase_code, email: $request->email, envatoUsername: $request->envato_username);
        $type = $res["success"] ? "success" : "danger";
        $message = $res["message"];
        //store information in database
        if (!empty($res["success"])) {
            //success verify
            $res["data"] = is_array($res["data"]) ? $res["data"] : (array)$res["data"];
            update_static_option("license_product_uuid", $res["data"]["product_uid"] ?? "");
            update_static_option("site_license_key", $res["data"]["license_key"] ?? "");
        }
        update_static_option("license_purchase_code", $request->envato_purchase_code);
        update_static_option("license_email", $request->email);
        update_static_option("license_username", $request->envato_username);

        return back()->with(["msg" => $message, "type" => $type]);
    }

    public function globalSearch()
    {
        $query = \request('query');

        $route_list = tenant() ? 'tenantAdminRoutes' : 'landlordAdminRoutes';

        $results = $this->searchRoutes(AdminGetRoutesEnum::$route_list(), e(strip_tags(trim($query))));

        return response()->json([
            'response' => $results
        ]);
    }

    private function searchRoutes($routes, $query) {
        $result = [];
        foreach($routes as $route => $text) {
            if(stripos($text, $query) !== false) {
                $result[route($route)] = $text;
            }
        }
        return $result;
    }

    public function unique_checker(Request $request)
    {
        $validated = $request->validate([
            'table' => 'required|max:25',
            'column' => 'required|max:25',
            'value' => 'required'
        ]);

        $tableName = e(strip_tags($validated['table']));
        $columnName = e(strip_tags($validated['column']));
        $query = e(strip_tags($validated['value']));

        $result = !DB::table($tableName)->select('id', $columnName)->where($columnName, $query)->exists();

        return response()->json([
            'msg' => $result ? __("The {$columnName} is available") : __("The {$columnName} is not available"),
            'type' => $result ? 'success' : 'danger'
        ]);
    }

    public function translateApi(Request $request)
    {
//        $this->validate($request, [
//            'langSlug' => 'required|string'
//        ]);

        $lang_name = 'en_GB';

        ini_set('max_execution_time', 900); // for infinite time of execution
        //todo: get all words from json file
        $file = json_decode(file_get_contents(resource_path('lang/') . $lang_name . '.json'), true);
        //chunk array into 100,
        $chunk_number = 20; //put maximum 20 in chunk
        $chunked_array = array_chunk($file, $chunk_number);

        foreach ($chunked_array as $ck_array) {
            $first_chunk = $ck_array;
            //todo api call to translate data
            $lang_slug = 'bn' ;//current(explode("_", $request->langSlug));
            try {
                $api_url = "https://translate.argosopentech.com/translate";
                $api_url2 = "https://translate.terraprint.co/translate";
                $res = Http::timeout(100)->retry(3, 100)->acceptJson()->post($api_url2, [
                    'q' => $first_chunk,
                    'source' => "en",
                    'target' => $lang_slug
                ]);
            } catch (\Exception $e) {

            }
            $translated_data = $res->object()->translatedText;
            foreach ($first_chunk as $key => $value) {
                if (file_exists(resource_path('lang/') . $lang_slug . '_translated.json')) {
                    $default_lang_data = file_get_contents(resource_path('lang') . '/' . $lang_slug . '_translated.json');
                    $default_lang_data = (array)json_decode($default_lang_data);

                    $default_lang_data[$value] = $translated_data[$key]; //append to the file directly

                    $default_lang_data = (object)$default_lang_data;
                    $default_lang_data = json_encode($default_lang_data);
                    file_put_contents(resource_path('lang/') . $lang_slug . '_translated.json', $default_lang_data);
                } else {
                    file_put_contents(resource_path('lang') . '/' . $lang_slug . '_translated.json', json_encode($first_chunk));
                }

            }//translate file generate loop

        }//chunk loop end


        dd('ok translate done');
    }
}
