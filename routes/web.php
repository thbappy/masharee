<?php

use App\Http\Controllers\Landlord\Admin\LandlordAdminController;
use App\Http\Controllers\Landlord\Frontend\LandlordFrontendController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Landlord\Frontend\PaymentLogController;
use Modules\SiteAnalytics\Http\Middleware\Analytics;
use Illuminate\Support\Facades\Http;
Route::middleware(['landlord_glvar','maintenance_mode'])->group(function (){
    Auth::routes(['register' => false]);
});

/* ---------------------------------
    landlord frontend login routes
----------------------------------- */
Route::middleware(['landlord_glvar','set_lang','maintenance_mode'])->controller(LandlordFrontendController::class)->group(function (){
    Route::get('/', 'homepage')->name('landlord.homepage');
    Route::post('/subdomain-check',  'subdomain_check')->name('landlord.subdomain.check');
    Route::post('/custom-domain-check',  'subdomain_custom_domain_check')->name('landlord.subdomain.custom-domain.check');
    Route::get('/verify-email','verify_user_email')->name('tenant.email.verify');
    Route::post('/verify-email','check_verify_user_email');
    Route::get('/resend-verify-email','resend_verify_user_email')->name('tenant.email.verify.resend');
    Route::post('store-login','ajax_login')->name('landlord.ajax.login');
    Route::get('/logout-from-landlord','logout_tenant_from_landlord')->name('tenant.admin.logout.from.landlord.home');
});

/* ---------------------------------------
    LANDLORD TO TENANT ADMIN TOKEN LOGIN
----------------------------------------- */
Route::get('/token-login/{token}', [LandlordFrontendController::class,'loginUsingToken'])->name('landlord.user.login.with.token');


/* -----------------------------
    landlord admin login routes
------------------------------ */
Route::middleware('set_lang')->controller(\App\Http\Controllers\Landlord\Admin\Auth\AdminLoginController::class)->prefix('admin')->group(function (){
    Route::get('/','login_form')->name('landlord.admin.login');
    Route::post('/','login_admin');
    Route::post('/logout','logout_admin')->name('landlord.admin.logout');

    Route::get('/login/forget-password','showUserForgetPasswordForm')->name('landlord.admin.forget.password');
    Route::get('/login/reset-password/{user}/{token}','showUserResetPasswordForm')->name('landlord.admin.reset.password');
    Route::post('/login/reset-password','UserResetPassword')->name('landlord.admin.reset.password.change');
    Route::post('/login/forget-password','sendUserForgetPasswordMail');
});


Route::controller(\App\Http\Controllers\Landlord\Frontend\FrontendFormController::class)->prefix('landlord')->group(function () {
    Route::post('submit-custom-form', 'custom_form_builder_message')->name('landlord.frontend.form.builder.custom.submit');
});


Route::prefix('user-home')->middleware(['auth:web','maintenance_mode','userMailVerify'])->controller(\App\Http\Controllers\Tenant\Admin\TenantDashboardController::class)->group(function (){
    Route::get('/','redirect_to_tenant_admin_panel')->name('tenant.home');
});



require_once __DIR__ .'/admin.php';

Route::middleware(['maintenance_mode','landlord_glvar'])->controller(PaymentLogController::class)->name('landlord.')->group(function () {
    Route::post('/paytm-ipn', 'paytm_ipn')->name('frontend.paytm.ipn');
    Route::post('/toyyibpay-ipn', 'toyyibpay_ipn')->name('frontend.toyyibpay.ipn');
    Route::get('/mollie-ipn', 'mollie_ipn')->name('frontend.mollie.ipn');
    Route::get('/stripe-ipn', 'stripe_ipn')->name('frontend.stripe.ipn');
    Route::post('/razorpay-ipn', 'razorpay_ipn')->name('frontend.razorpay.ipn');
    Route::post('/payfast-ipn', 'payfast_ipn')->name('frontend.payfast.ipn');
    Route::get('/flutterwave/ipn', 'flutterwave_ipn')->name('frontend.flutterwave.ipn');
    Route::get('/paystack-ipn', 'paystack_ipn')->name('frontend.paystack.ipn');
    Route::get('/midtrans-ipn', 'midtrans_ipn')->name('frontend.midtrans.ipn');
    Route::post('/cashfree-ipn', 'cashfree_ipn')->name('frontend.cashfree.ipn');
    Route::get('/instamojo-ipn', 'instamojo_ipn')->name('frontend.instamojo.ipn');
    Route::get('/paypal-ipn', 'paypal_ipn')->name('frontend.paypal.ipn');
    Route::get('/marcadopago-ipn', 'marcadopago_ipn')->name('frontend.marcadopago.ipn');
    Route::get('/squareup-ipn', 'squareup_ipn')->name('frontend.squareup.ipn');
    Route::post('/cinetpay-ipn', 'cinetpay_ipn')->name('frontend.cinetpay.ipn');
    Route::post('/paytabs-ipn', 'paytabs_ipn')->name('frontend.paytabs.ipn');
    Route::post('/billplz-ipn', 'billplz_ipn')->name('frontend.billplz.ipn');
    Route::post('/zitopay-ipn', 'zitopay_ipn')->name('frontend.zitopay.ipn');
    Route::post('/iyzipay-ipn', 'iyzipay_ipn')->name('frontend.iyzipay.ipn');
    Route::post('/order-confirm','order_payment_form')->name('frontend.order.payment.form')->middleware('set_lang');
});


//LANDLORD HOME PAGE FRONTEND TENANT LOGIN - REGISTRATION
Route::middleware(['landlord_glvar','set_lang','maintenance_mode'])->controller(\App\Http\Controllers\Landlord\Frontend\LandlordFrontendController::class)->name('landlord.')->group(function () {
    Route::get('/login', 'showTenantLoginForm')->name('user.login');
    Route::post('store-login','ajax_login')->name('user.ajax.login');
    Route::get('/register','showTenantRegistrationForm')->name('user.register');
    Route::post('/register-store','tenant_user_create')->name('user.register.store');
    Route::get('/logout','tenant_logout')->name('user.logout');

    Route::get('/login/forget-password','showUserForgetPasswordForm')->name('user.forget.password');
    Route::get('/login/reset-password/{user}/{token}','showUserResetPasswordForm')->name('user.reset.password');
    Route::post('/login/reset-password','UserResetPassword')->name('user.reset.password.change');
    Route::post('/login/forget-password','sendUserForgetPasswordMail');

    Route::get('/user-logout','user_logout')->name('frontend.user.logout');

    Route::get('/verify-email','verify_user_email')->name('user.email.verify');
    Route::post('/verify-email','check_verify_user_email');
    Route::get('/resend-verify-email','resend_verify_user_email')->name('user.email.verify.resend');

    //Order
    Route::get('/plan-order/{id}','plan_order')->name('frontend.plan.order');
    //payment status route
    Route::get('/order-success/{id}','order_payment_success')->name('frontend.order.payment.success');
    Route::get('/order-cancel/{id}','order_payment_cancel')->name('frontend.order.payment.cancel');
    Route::get('/order-cancel-static','order_payment_cancel_static')->name('frontend.order.payment.cancel.static');
    Route::get('/order-confirm/{id}','order_confirm')->name('frontend.order.confirm');

    // Trial Account
    Route::post('/user/trial/account', 'user_trial_account')->name('frontend.trial.account');

    // Coupon Apply
    Route::get('/apply-coupon', 'applyCoupon')->name('frontend.coupon.apply');
});

// LANDLORD HOME PAGE Tenant Dashboard Routes
Route::controller(\App\Http\Controllers\Landlord\Frontend\UserDashboardController::class)->middleware(['landlord_glvar','set_lang','maintenance_mode','tenantMailVerify'])->name('landlord.')->group(function(){
    Route::get('/user-home', 'user_index')->name('user.home');
    Route::get('/user/download/file/{id}', 'download_file')->name('user.dashboard.download.file');
    Route::get('/user/change-password', 'change_password')->name('user.home.change.password');
    Route::get('/user/edit-profile', 'edit_profile')->name('user.home.edit.profile');
    Route::post('/user/profile-update', 'user_profile_update')->name('user.profile.update');
    Route::post('/user/password-change', 'user_password_change')->name('user.password.change');
    Route::get('/user/support-tickets', 'support_tickets')->name('user.home.support.tickets');
    Route::get('support-ticket/view/{id}', 'support_ticket_view')->name('user.dashboard.support.ticket.view');
    Route::post('support-ticket/priority-change', 'support_ticket_priority_change')->name('user.dashboard.support.ticket.priority.change');
    Route::post('support-ticket/status-change', 'support_ticket_status_change')->name('user.dashboard.support.ticket.status.change');
    Route::post('support-ticket/message', 'support_ticket_message')->name('user.dashboard.support.ticket.message');
    Route::get('/package-orders', 'package_orders')->name('user.dashboard.package.order');
    Route::get('/custom-domain', 'custom_domain')->name('user.dashboard.custom.domain');
    Route::post('/custom-domain', 'submit_custom_domain');
    Route::post('/package-order/cancel', 'package_order_cancel')->name('user.dashboard.package.order.cancel');
    Route::post('/package-user/generate-invoice', 'generate_package_invoice')->name('frontend.package.invoice.generate');

    Route::post('/package/check', 'package_check')->name('frontend.package.check');
});

//User Support Ticket Routes
Route::controller(\App\Http\Controllers\Landlord\Frontend\SupportTicketController::class)->middleware(['landlord_glvar', 'set_lang'])->name('landlord.')->group(function(){
    Route::get('support-tickets', 'page')->name('frontend.support.ticket');
    Route::post('support-tickets/new', 'store')->name('frontend.support.ticket.store');
});


//Visitor Newsletter Routes
Route::controller(\App\Http\Controllers\Landlord\Frontend\LandlordFrontendController::class)->middleware('landlord_glvar')->name('landlord.')->group(function(){
    Route::post('newsletter/new', 'newsletter_store')->name('frontend.newsletter.store.ajax');
});


//single page route
Route::middleware(['landlord_glvar','set_lang','maintenance_mode'])->controller(\App\Http\Controllers\Landlord\Frontend\LandlordFrontendController::class)->name('landlord.')->group(function () {
    //payment page route
    Route::get('/plan-order/{id}','plan_order')->name('frontend.plan.order');
    Route::get('/order-success/{id}','order_payment_success')->name('frontend.order.payment.success');
    Route::get('/order-cancel/{id}','order_payment_cancel')->name('frontend.order.payment.cancel');
    Route::get('/order-cancel-static','order_payment_cancel_static')->name('frontend.order.payment.cancel.static');
    Route::get('/view-plan/{id}/{trial?}','view_plan')->name('frontend.plan.view');
    Route::get('/order-confirm/{id}','order_confirm')->name('frontend.order.confirm');
    Route::get('/lang-change','lang_change')->name('langchange');
    Route::get('/{page:slug}', 'dynamic_single_page')->name('dynamic.page');
});

Route::get("assets/theme/screenshot/{theme}", function ($theme){
    $themeData = renderPrimaryThemeScreenshot($theme);
    $image_name = last(explode('/',$themeData));

    if(file_exists(str_replace('/assets','/screenshot', theme_assets($image_name, $theme)))){
        return response()->file(str_replace('/assets','/screenshot', theme_assets($image_name, $theme)));
    }

    return abort(404);
})->name("theme.primary.screenshot");

Route::get("assets/payment-gateway/screenshot/{moduleName}/{gatewayName}", function ($moduleName, $gatewayName){
    $image_name = getPaymentGatewayImagePath($gatewayName);
    $module_path = module_path($moduleName).'/assets/payment-gateway-image/'.$image_name;

    if(file_exists($module_path)){
        return response()->file($module_path);
    }

    return abort(404);
})->name("payment.gateway.logo");



Route::get("test/ship", function (){

    #moc server

  $arrayVar = [
        "plannedShippingDateAndTime" => "2022-10-19T19:19:40 GMT+00:00",
        "pickup" => ["isRequested" => false],
        "productCode" => "P",
        "accounts" => [["typeCode" => "shipper", "number" => "123456789"]],


        "customerDetails" => [
            "shipperDetails" => [
                "postalAddress" => [
                    "postalCode" => "526238",
                    "cityName" => "Zhaoqing",
                    "countryCode" => "CN",
                    "addressLine1" =>
                        "4FENQU, 2HAOKU, WEIPINHUI WULIU YUAN，DAWANG",
                    "countyName" => "SIHUI",
                    "countryName" => "CHINA, PEOPLES REPUBLIC",
                ],
                "contactInformation" => [
                    "email" => "shipper_create_shipmentapi@dhltestmail.com",
                    "phone" => "18211309039",
                    "companyName" => "Cider BookStore",
                    "fullName" => "LiuWeiMing",
                ],

            ],
            "receiverDetails" => [
                "postalAddress" => [
                    "cityName" => "Graford",
                    "countryCode" => "US",
                    "postalCode" => "76449",
                    "addressLine1" => "116 Marine Dr",
                    "countryName" => "UNITED STATES OF AMERICA",
                ],
                "contactInformation" => [
                    "email" => "recipient_create_shipmentapi@dhltestmail.com",
                    "phone" => "9402825665",
                    "companyName" => "Baylee Marshall",
                    "fullName" => "Baylee Marshall",
                ],


            ],
        ],
        // "content" => [
        //     "packages" => [
        //         [
        //             "typeCode" => "2BP",
        //             "weight" => 0.5,
        //             "dimensions" => ["length" => 1, "width" => 1, "height" => 1],
        //             "customerReferences" => [
        //                 ["value" => "3654673", "typeCode" => "CU"],
        //             ],
        //             "description" => "Piece content description",
        //             "labelDescription" => "bespoke label description",
        //         ],
        //     ],
        //     "isCustomsDeclarable" => true,
        //     "declaredValue" => 120,
        //     "declaredValueCurrency" => "USD",
        //     "exportDeclaration" => [
        //         "lineItems" => [
        //             [
        //                 "number" => 1,
        //                 "description" => "Harry Steward biography first edition",
        //                 "price" => 15,
        //                 "quantity" => ["value" => 4, "unitOfMeasurement" => "GM"],
        //                 "commodityCodes" => [
        //                     ["typeCode" => "outbound", "value" => "84713000"],
        //                     ["typeCode" => "inbound", "value" => "5109101110"],
        //                 ],
        //                 "exportReasonType" => "permanent",
        //                 "manufacturerCountry" => "US",
        //                 "exportControlClassificationNumber" => "US123456789",
        //                 "weight" => ["netValue" => 0.1, "grossValue" => 0.7],
        //                 "isTaxesPaid" => true,
        //                 "additionalInformation" => ["450pages"],
        //                 "customerReferences" => [
        //                     ["typeCode" => "AFE", "value" => "1299210"],
        //                 ],
        //                 "customsDocuments" => [
        //                     [
        //                         "typeCode" => "COO",
        //                         "value" => "MyDHLAPI - LN#1-CUSDOC-001",
        //                     ],
        //                 ],
        //             ],
        //             [
        //                 "number" => 2,
        //                 "description" => "Andromeda Chapter 394 - Revenge of Brook",
        //                 "price" => 15,
        //                 "quantity" => ["value" => 4, "unitOfMeasurement" => "GM"],
        //                 "commodityCodes" => [
        //                     ["typeCode" => "outbound", "value" => "6109100011"],
        //                     ["typeCode" => "inbound", "value" => "5109101111"],
        //                 ],
        //                 "exportReasonType" => "permanent",
        //                 "manufacturerCountry" => "US",
        //                 "exportControlClassificationNumber" => "US123456789",
        //                 "weight" => ["netValue" => 0.1, "grossValue" => 0.7],
        //                 "isTaxesPaid" => true,
        //                 "additionalInformation" => ["36pages"],
        //                 "customerReferences" => [
        //                     ["typeCode" => "AFE", "value" => "1299211"],
        //                 ],
        //                 "customsDocuments" => [
        //                     [
        //                         "typeCode" => "COO",
        //                         "value" => "MyDHLAPI - LN#1-CUSDOC-001",
        //                     ],
        //                 ],
        //             ],
        //         ],
        //         "invoice" => [
        //             "number" => "2667168671",
        //             "date" => "2022-10-22",
        //             "instructions" => ["Handle with care"],
        //             "totalNetWeight" => 0.4,
        //             "totalGrossWeight" => 0.5,
        //             "customerReferences" => [
        //                 ["typeCode" => "UCN", "value" => "UCN-783974937"],
        //                 ["typeCode" => "CN", "value" => "CUN-76498376498"],
        //                 ["typeCode" => "RMA", "value" => "MyDHLAPI-TESTREF-001"],
        //             ],
        //             "termsOfPayment" => "100 days",
        //             "indicativeCustomsValues" => [
        //                 "importCustomsDutyValue" => 150.57,
        //                 "importTaxesValue" => 49.43,
        //             ],
        //         ],
        //         "remarks" => [["value" => "Right side up only"]],
        //         "additionalCharges" => [
        //             ["value" => 10, "caption" => "fee", "typeCode" => "freight"],
        //             [
        //                 "value" => 20,
        //                 "caption" => "freight charges",
        //                 "typeCode" => "other",
        //             ],
        //             [
        //                 "value" => 10,
        //                 "caption" => "ins charges",
        //                 "typeCode" => "insurance",
        //             ],
        //             [
        //                 "value" => 7,
        //                 "caption" => "rev charges",
        //                 "typeCode" => "reverse_charge",
        //             ],
        //         ],
        //         "destinationPortName" => "New York Port",
        //         "placeOfIncoterm" => "ShenZhen Port",
        //         "payerVATNumber" => "12345ED",
        //         "recipientReference" => "01291344",
        //         "exporter" => ["id" => "121233", "code" => "S"],
        //         "packageMarks" => "Fragile glass bottle",
        //         "declarationNotes" => [
        //             ["value" => "up to three declaration notes"],
        //         ],
        //         "exportReference" => "export reference",
        //         "exportReason" => "export reason",
        //         "exportReasonType" => "permanent",
        //         "licenses" => [["typeCode" => "export", "value" => "123127233"]],
        //         "shipmentType" => "personal",
        //         "customsDocuments" => [
        //             ["typeCode" => "INV", "value" => "MyDHLAPI - CUSDOC-001"],
        //         ],
        //     ],
        //     "description" => "Shipment",
        //     "USFilingTypeValue" => "12345",
        //     "incoterm" => "DAP",
        //     "unitOfMeasurement" => "metric",
        // ],

        "getTransliteratedResponse" => false,
        "estimatedDeliveryDate" => ["isRequested" => true, "typeCode" => "QDDC"],

    ];
    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'Message-Reference' => 'd0e7832e-5c98-11ea-bc55-0242ac13',
        'Message-Reference-Date' => 'Wed, 21 Oct 2015 07:28:00 GMT',
        'Plugin-Name' => '',
        'Plugin-Version' => '',
        'Shipping-System-Platform-Name' => '',
        'Shipping-System-Platform-Version' => '',
        'Webstore-Platform-Name' => '',
        'Webstore-Platform-Version' => '',
        'Authorization' => 'Basic ZGVtby1rZXk6ZGVtby1zZWNyZXQ=', // Base64 encoded 'demo-key:demo-secret'
    ])->post('https://api-mock.dhl.com/mydhlapi/shipments',88);

        @dd( $response->json());







});


// function generateRandomString($length = 10) {
//     $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
//     $randomString = '';
//     for ($i = 0; $i < $length; $i++) {
//         $randomString .= $characters[rand(0, strlen($characters) - 1)];
//     }
//     return $randomString;
// }






































