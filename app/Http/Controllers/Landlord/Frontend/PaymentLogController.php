<?php

namespace App\Http\Controllers\Landlord\Frontend;

use App\Actions\Payment\PaymentGateways;
use App\Actions\Sms\SmsSendAction;
use App\Enums\LandlordCouponType;
use App\Events\TenantRegisterEvent;
use App\Facades\ModuleDataFacade;
use App\Helpers\FlashMsg;
use App\Helpers\Payment\DatabaseUpdateAndMailSend\LandlordPricePlanAndTenantCreate;
use App\Helpers\Payment\PaymentGatewayCredential;
use App\Helpers\TenantHelper\TenantHelpers;
use App\Http\Controllers\Controller;
use App\Mail\BasicMail;
use App\Mail\PlaceOrder;
use App\Mail\TenantCredentialMail;
use App\Models\Coupon;
use App\Models\FormBuilder;
use App\Models\PaymentGateway;
use App\Models\PaymentLogs;
use App\Models\PricePlan;
use App\Models\Tenant;
use App\Models\TenantException;
use App\Models\User;
use App\Models\ZeroPricePlanHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\SmsGateway\Http\Traits\OtpGlobalTrait;
use Modules\Wallet\Entities\Wallet;
use Modules\Wallet\Entities\WalletSettings;
use Modules\Wallet\Entities\WalletTenantList;
use Modules\Wallet\Http\Controllers\Frontend\BuyerWalletPaymentController;
use Modules\Wallet\Http\Services\WalletService;
use Xgenious\Paymentgateway\Facades\XgPaymentGateway;


class PaymentLogController extends Controller
{
    private const CANCEL_ROUTE = 'landlord.frontend.order.payment.cancel';
    private const SUCCESS_ROUTE = 'landlord.frontend.order.payment.success';

    private float $total;
    private object $payment_details;
    protected function cancel_page()
    {
        return redirect()->route('landlord.frontend.order.payment.cancel.static');
    }

    public function order_payment_form(Request $request)
    {
        $manual_transection_condition = $request->selected_payment_gateway == 'manual_payment' ? 'required' : 'nullable';
        $request_pack_id = $request->package_id;
        $plan = PricePlan::findOrFail($request_pack_id);

        $zero_price_condition = 'required';
        if($plan->price == 0)
        {
            $manual_transection_condition = 'nullable';
            $zero_price_condition = 'nullable';
            $request->selected_payment_gateway = 'manual_payment';

            $purchased_packaged = ZeroPricePlanHistory::where('user_id', Auth::guard('web')->user()->id)->count();
            $zero_plan_limit = get_static_option('zero_plan_limit');

            if ($purchased_packaged >= $zero_plan_limit)
            {
                return back()->with(FlashMsg::explain('danger', __('Sorry! You can not purchase more free plan.')));
            }
        }

        $selected_payment_gateway = 'nullable';
        if ($request->selected_payment_gateway && $request->selected_payment_gateway != 'manual_payment')
        {
            $zero_price_condition = 'nullable';
            $selected_payment_gateway = 'required';
        }

        $request->validate([
            'name' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'theme_slug' => ['required', Rule::in(getAllThemeSlug())],
            'package_id' => 'required|string',
            'payment_gateway' => ''.$zero_price_condition.'|string',
            'selected_payment_gateway' => ''.$selected_payment_gateway.'|string',
            'trasaction_id' => '' . $manual_transection_condition . '',
            'trasaction_attachment' => '' . $manual_transection_condition . '|mimes:jpeg,png,jpg,gif|max:2048',
            'subdomain' => "required_if:custom_subdomain,!=,null",
            'custom_subdomain' => "required_if:subdomain,==,custom_domain__dd",
            'coupon' => "nullable"
        ], [
            "custom_subdomain.required_if" => __("Custom Sub Domain Required."),
            "trasaction_id" => __("Transaction ID Required."),
            "trasaction_attachment.required" => __("Transaction Attachment Required."),
            "theme_slug.in" => __("The selected theme is invalid.")
        ]);

        if ($request->custom_subdomain == null) {
            $request->validate([
                'subdomain' => 'required'
            ]);

            $existing_lifetime_plan = PaymentLogs::where(['tenant_id' => $request->subdomain, 'payment_status' => 'complete', 'expire_date' => null])->first();
            if ($existing_lifetime_plan != null) {
                return back()->with(['type' => 'danger', 'msg' => __('You are already using a lifetime plan')]);
            }
        }

        if ($request->custom_subdomain != null) {
            $has_subdomain = Tenant::find(trim($request->custom_subdomain));
            if (!empty($has_subdomain)) {
                return back()->with(['type' => 'danger', 'msg' => __('This subdomain is already in use, Try something different')]);
            }

            $site_domain = url('/');
            $site_domain = str_replace(['http://', 'https://'], '', $site_domain);
            $site_domain = substr($site_domain, 0, strpos($site_domain, '.'));
            $restricted_words = ['https', 'http', 'http://', 'https://','www', 'subdomain', 'domain', 'primary-domain', 'central-domain',
                'landlord', 'landlords', 'tenant', 'tenants', 'admin',
                'user', 'users', $site_domain];

            if (in_array(trim($request->custom_subdomain), $restricted_words))
            {
                return back()->with(FlashMsg::explain('danger', __('Sorry, You can not use this subdomain')));
            }

            $auth_user = Auth::guard('web')->user();
            if (!empty(get_static_option('user_email_verify_status')) && !$auth_user->email_verified)
            {
                return back()->with(FlashMsg::explain('danger', __('Please verify your account, Visit user dashboard for verification')));
            }

            $sub = $request->custom_subdomain;
            $check_type = false;
            for ($i=0; $i<strlen($sub); $i++)
            {
                if(ctype_alnum($sub[$i])) {
                    $check_type = true;
                }
            }

            if ($check_type == false)
            {
                return back()->with(FlashMsg::explain('danger', __('Sorry, You can not use this subdomain')));
            }
        }

        $order_details = $plan ?? '';

        $package_start_date = '';
        $package_expire_date = '';

        if (!empty($order_details)) {
            if ($order_details->type == 0) {
                //monthly
                $package_start_date = Carbon::now()->format('d-m-Y h:i:s');
                $package_expire_date = Carbon::now()->addMonth(1)->format('d-m-Y h:i:s');

            } elseif ($order_details->type == 1) {
                //yearly
                $package_start_date = Carbon::now()->format('d-m-Y h:i:s');
                $package_expire_date = Carbon::now()->addYear(1)->format('d-m-Y h:i:s');
            } else {
                //lifetime
                $package_start_date = Carbon::now()->format('d-m-Y h:i:s');
                $package_expire_date = null;
            }
        }

        if ($request->subdomain != 'custom_domain__dd') {
            $subdomain = Str::slug($request->subdomain);
        } else {
            $subdomain = Str::slug($request->custom_subdomain);
        }

        $amount_to_charge = $this->applyCoupon($request->coupon ,$order_details->price);
        $this->total = $amount_to_charge;
        $request_date_remove = $request;

        $selected_payment_gateway = $request_date_remove['selected_payment_gateway'] ?? $request_date_remove['payment_gateway'];
        if ($selected_payment_gateway == null) {
            $selected_payment_gateway = 'manual_payment';
        }

        $package_id = $request_date_remove['package_id'];
        $name = $request_date_remove['name'];
        $email = $request_date_remove['email'];
        $trasaction_id = $request_date_remove['trasaction_id'];

        if ($request->trasaction_attachment != null) {
            $image = $request->file('trasaction_attachment');
            $image_extenstion = $image->extension();
            $image_name_with_ext = $image->getClientOriginalName();

            $image_name = pathinfo($image_name_with_ext, PATHINFO_FILENAME);
            $image_name = strtolower(Str::slug($image_name));
            $image_db = $image_name . time() . '.' . $image_extenstion;

            $path = global_assets_path('assets/landlord/uploads/payment_attachments/');
            $image->move($path, $image_db);
        }
        $trasaction_attachment = $image_db ?? null;

        unset($request_date_remove['custom_form_id']);
        unset($request_date_remove['payment_gateway']);
        unset($request_date_remove['package_id']);
        unset($request_date_remove['package']);
        unset($request_date_remove['pkg_user_name']);
        unset($request_date_remove['pkg_user_email']);
        unset($request_date_remove['name']);
        unset($request_date_remove['email']);
        unset($request_date_remove['trasaction_id']);
        unset($request_date_remove['trasaction_attachment']);

        $auth = auth()->guard('web')->user();
        $auth_id = $auth->id;
        $old_tenant_log = PaymentLogs::where(['user_id' => $auth_id, 'tenant_id' => $subdomain])->latest()->first() ?? '';

        $tenantHelper = TenantHelpers::init()->setTenantId($subdomain)
            ->setPackage($plan)
            ->setPaymentLog($old_tenant_log)
            ->setTheme($request->theme_slug);

        $package_start_date = $tenantHelper->getStartDate();
        $package_expire_date = $tenantHelper->getExpiredDate();

        $is_tenant = Tenant::find($subdomain);

        DB::beginTransaction(); // Starting all the actions as safe translations
        try {
            // Exising Tenant + Plan
            if (!is_null($is_tenant)) {
                $old_tenant_log = PaymentLogs::where(['user_id' => $auth_id, 'tenant_id' => $is_tenant->id])->latest()->first() ?? '';

                // If Payment Renewing
                if (!empty($old_tenant_log->package_id) == $request_pack_id && !empty($old_tenant_log->user_id) && $old_tenant_log->user_id == $auth_id && ($old_tenant_log->payment_status == 'complete' || $old_tenant_log->status == 'trial')) {
                    if ($package_expire_date != null) {
                        $old_days_left = Carbon::now()->diff($old_tenant_log->expire_date);
                        $left_days = 0;

                        if ($old_days_left->invert == 0) {
                            $left_days = $old_days_left->days;
                        }

                        $renew_left_days = 0;
                        $renew_left_days = Carbon::parse($package_expire_date)->diffInDays();

                        $sum_days = $left_days + $renew_left_days;
                        $new_package_expire_date = Carbon::today()->addDays($sum_days)->format("d-m-Y h:i:s");
                    } else {
                        $new_package_expire_date = null;
                    }

                    PaymentLogs::findOrFail($old_tenant_log->id)->update([
                        'email' => $email,
                        'name' => $name,
                        'package_name' => $order_details->title,
                        'package_price' => $amount_to_charge,
                        'package_gateway' => $selected_payment_gateway,
                        'package_id' => $package_id,
                        'user_id' => auth()->guard('web')->user()->id ?? null,
                        'tenant_id' => $subdomain ?? null,
                        'theme_slug' => $old_tenant_log->theme_slug,
                        'status' => 'pending',
                        'payment_status' => 'pending',
                        'renew_status' => is_null($old_tenant_log->renew_status) ? 1 : $old_tenant_log->renew_status + 1,
                        'is_renew' => 1,
                        'track' => Str::random(10),
                        'updated_at' => Carbon::now(),
                        'start_date' => $package_start_date,
                        'expire_date' => $new_package_expire_date
                    ]);

                    $payment_details = PaymentLogs::findOrFail($old_tenant_log->id);
                    $this->payment_details = $payment_details;
                } // If Payment Pending
                elseif (!empty($old_tenant_log) && $old_tenant_log->payment_status == 'pending') {
                    PaymentLogs::findOrFail($old_tenant_log->id)->update([
                        'email' => $email,
                        'name' => $name,
                        'package_name' => $order_details->title,
                        'package_price' => $amount_to_charge,
                        'package_gateway' => $selected_payment_gateway,
                        'package_id' => $package_id,
                        'user_id' => auth()->guard('web')->user()->id ?? null,
                        'tenant_id' => $subdomain ?? null,
                        'theme_slug' => $old_tenant_log->theme_slug,
                        'status' => 'pending',
                        'payment_status' => 'pending',
                        'is_renew' => $old_tenant_log->renew_status != null ? 1 : 0,
                        'track' => Str::random(10),
                        'updated_at' => Carbon::now(),
                        'start_date' => $package_start_date,
                        'expire_date' => $package_expire_date
                    ]);

                    $payment_details = PaymentLogs::findOrFail($old_tenant_log->id);
                    $this->payment_details = $payment_details;
                }
            } // New Tenant + Plan (New Payment)
            else {
                $old_tenant_log = PaymentLogs::where(['user_id' => $auth_id, 'tenant_id' => trim($request->custom_subdomain)])->latest()->first();
                if (empty($old_tenant_log)) {
                    $payment_log_id = PaymentLogs::create([
                        'email' => $email,
                        'name' => $name,
                        'package_name' => $order_details->title,
                        'package_price' => $amount_to_charge,
                        'package_gateway' => $selected_payment_gateway,
                        'package_id' => $package_id,
                        'user_id' => auth()->guard('web')->user()->id ?? null,
                        'tenant_id' => $subdomain ?? null,
                        'theme_slug' => $request->theme_slug,
                        'status' => ($package_id == 10) ? 'complete' : 'pending',
                        'payment_status' => ($package_id == 10) ? 'complete' : 'pending',
                        'is_renew' => 0,
                        'track' => Str::random(10),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'start_date' => $package_start_date,
                        'expire_date' => $package_expire_date,
                    ])->id;

                    $payment_details = PaymentLogs::findOrFail($payment_log_id);
                    $this->payment_details = $payment_details;
                    
                    if($package_id == 10){
                        $msg = __('Payment status changed successfully..!');
                        if ($payment_details->payment_status == 'complete') {
                            (new PaymentGateways())->tenant_create_event_with_credential_mail($payment_details->id);
                            $payment_details->order_id = $payment_details->id;
                            (new PaymentGateways())->update_tenant($payment_details);
            
                            $msg .= ' ' . __('And a new tenant has been created for the payment log');
                        }
                    }

                } else {
                    $old_tenant_log->update([
                        'email' => $email,
                        'name' => $name,
                        'package_name' => $order_details->title,
                        'package_price' => $amount_to_charge,
                        'package_gateway' => $selected_payment_gateway,
                        'package_id' => $package_id,
                        'user_id' => auth()->guard('web')->user()->id ?? null,
                        'status' => ($package_id == 10) ? 'complete' : 'pending',
                        'payment_status' => ($package_id == 10) ? 'complete' : 'pending',
                        'is_renew' => 0,
                        'track' => Str::random(10),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'start_date' => $package_start_date,
                        'expire_date' => $package_expire_date,
                    ]);

                    $payment_details = PaymentLogs::findOrFail($old_tenant_log->id);
                    $this->payment_details = $payment_details;
                    
                    if($package_id == 10){
                        $msg = __('Payment status changed successfully..!');
                        if ($payment_details->payment_status == 'complete') {
                            (new PaymentGateways())->tenant_create_event_with_credential_mail($payment_details->id);
                            $payment_details->order_id = $payment_details->id;
                            (new PaymentGateways())->update_tenant($payment_details);
            
                            $msg .= ' ' . __('And a new tenant has been created for the payment log');
                        }
                    }
                }
            }

            DB::commit(); // Committing all the actions
        } catch (\Exception $exception) {
            DB::rollBack(); // Rollback all the actions
            return back()->with('msg', __('Something went wrong'));
        }

        if(!isset($this->payment_details))
        {
            TenantException::create([
                'tenant_id' => $is_tenant->id,
                'issue_type' => __('Payment log creation unsuccessful'),
                'description' => __('Payment log creation unsuccessful but tenant and domain created'),
                'domain_create_status' => 1,
                'seen_status' => 0
            ]);
            return back()->with(['msg' => __('Your shop creation was done incorrectly. Please contact admin or create a new shop'), 'type' => 'danger']);
        }

        if ($request->selected_payment_gateway === 'manual_payment')
        {
            PaymentLogs::find($this->payment_details['id'])->update([
                'transaction_id' => $trasaction_id ?? '',
                'attachments' => $trasaction_attachment ?? '',
            ]);

            if ($this->payment_details['price'] == 0)
            {
                ZeroPricePlanHistory::create([
                    'user_id' => $this->payment_details['user_id'],
                    'plan_id' => $this->payment_details['package_id'],
                ]);
            }

            try {
                (new PaymentGateways())->send_order_mail($this->payment_details['id']);
            } catch (\Exception $e) {}

            return redirect()->route(self::SUCCESS_ROUTE, wrap_random_number($this->payment_details['id']));
        } else {
            return $this->payment_with_gateway($request->selected_payment_gateway, $request->all());
        }
    }

    public function payment_with_gateway($payment_gateway_name, $request = [])
    {
        try {
            $gateway_function = 'get_' . $payment_gateway_name . '_credential';

            if (!method_exists((new PaymentGatewayCredential()), $gateway_function))
            {
                $custom_data['request'] = $request;
                $custom_data['payment_details'] = $this->payment_details->toArray();
                $custom_data['total'] = $this->total;
                $custom_data['payment_type'] = "price_plan";
                $custom_data['payment_for'] = "landlord";
                $custom_data['cancel_url'] = route(self::CANCEL_ROUTE, random_int(111111,999999).$this->payment_details['id'].random_int(111111,999999));
                $custom_data['success_url'] = route(self::SUCCESS_ROUTE, random_int(111111,999999).$this->payment_details['id'].random_int(111111,999999));

                $charge_customer_class_namespace = getChargeCustomerMethodNameByPaymentGatewayNameSpace($payment_gateway_name);
                $charge_customer_method_name = getChargeCustomerMethodNameByPaymentGatewayName($payment_gateway_name);

                abort_if(empty($charge_customer_method_name), 403); // If custom payment gateway not found

                $custom_charge_customer_class_object = new $charge_customer_class_namespace;
                if(class_exists($charge_customer_class_namespace) && method_exists($custom_charge_customer_class_object, $charge_customer_method_name))
                {
                    return $custom_charge_customer_class_object->$charge_customer_method_name($custom_data);
                } else {
                    return back()->with(FlashMsg::explain('danger', 'Incorrect Class or Method'));
                }
            } else {
                $gateway = PaymentGatewayCredential::$gateway_function();
                $redirect_url = $gateway->charge_customer(
                    $this->common_charge_customer_data($payment_gateway_name)
                );

                return $redirect_url;
            }
        } catch (\Exception $e) {
            return back()->with(['msg' => $e->getMessage(), 'type' => 'danger']);
        }
    }

    public function common_charge_customer_data($payment_gateway_name)
    {
        $user = Auth::guard('web')->user();
        $email = $user->email;
        $name = $user->name;

        return [
            'amount' => $this->total,
            'title' => $this->payment_details['package_name'],
            'description' => 'Payment For Package Order Id: #' . $this->payment_details['id'] . ' Package Name: ' . $this->payment_details['package_name']  . ' Payer Name: ' . $this->payment_details['name']  . ' Payer Email:' . $this->payment_details['email'] ,
            'ipn_url' => route('landlord.frontend.' . strtolower($payment_gateway_name) . '.ipn', $this->payment_details['id']),
            'order_id' => $this->payment_details['id'],
            'track' => \Str::random(36),
            'cancel_url' => route(self::CANCEL_ROUTE, random_int(111111,999999).$this->payment_details['id'].random_int(111111,999999)),
            'success_url' => route(self::SUCCESS_ROUTE, random_int(111111,999999).$this->payment_details['id'].random_int(111111,999999)),
            'email' => $email,
            'name' => $name,
            'payment_type' => 'order',
        ];
    }


    // IPNs
    public function paypal_ipn()
    {
        $paypal = PaymentGatewayCredential::get_paypal_credential();
        $payment_data = $paypal->ipn_response();

        // todo: Implement it to every ipn method
        try{
            return $this->common_ipn_data($payment_data);
        }catch(\Exception $e){

        }
    }

    public function paytm_ipn()
    {
        $paytm = PaymentGatewayCredential::get_paytm_credential();
        $payment_data = $paytm->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function flutterwave_ipn()
    {
        $flutterwave = PaymentGatewayCredential::get_flutterwave_credential();
        $payment_data = $flutterwave->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function stripe_ipn()
    {
        $stripe = PaymentGatewayCredential::get_stripe_credential();
        $payment_data = $stripe->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function razorpay_ipn()
    {
        $razorpay = PaymentGatewayCredential::get_razorpay_credential();
        $payment_data = $razorpay->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function paystack_ipn()
    {
        $paystack = PaymentGatewayCredential::get_paystack_credential();
        $payment_data = $paystack->ipn_response();

        return $this->paystack_action($payment_data);
    }

    public function payfast_ipn()
    {
        $payfast = PaymentGatewayCredential::get_payfast_credential();
        $payment_data = $payfast->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function mollie_ipn()
    {
        $mollie = PaymentGatewayCredential::get_mollie_credential();

        // todo: Implement it to every ipn method
//        try{
            $payment_data = $mollie->ipn_response();
            return $this->common_ipn_data($payment_data);
//        }catch(\Exception $e){
//            $this->cancel_page();
//        }
    }

    public function midtrans_ipn()
    {
        $midtrans = PaymentGatewayCredential::get_midtrans_credential();
        $payment_data = $midtrans->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function cashfree_ipn()
    {
        $cashfree = PaymentGatewayCredential::get_cashfree_credential();
        $payment_data = $cashfree->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function instamojo_ipn()
    {
        $instamojo = PaymentGatewayCredential::get_instamojo_credential();
        $payment_data = $instamojo->ipn_response();
        return $this->common_ipn_data($payment_data);
    }
    public function marcadopago_ipn()
    {
        $marcadopago = PaymentGatewayCredential::get_marcadopago_credential();

        try {
            $payment_data = $marcadopago->ipn_response();
            return $this->common_ipn_data($payment_data);
        } catch (\Exception $exception)
        {
            $this->cancel_page();
        }
    }
    public function squareup_ipn()
    {
        $squareup = PaymentGatewayCredential::get_squareup_credential();
        $payment_data = $squareup->ipn_response();
        return $this->common_ipn_data($payment_data);
    }
    public function cinetpay_ipn()
    {
        $cinetpay = PaymentGatewayCredential::get_cinetpay_credential();
        $payment_data = $cinetpay->ipn_response();
        return $this->common_ipn_data($payment_data);
    }
    public function paytabs_ipn()
    {
        $paytabs = PaymentGatewayCredential::get_paytabs_credential();
        $payment_data = $paytabs->ipn_response();
        return $this->common_ipn_data($payment_data);
    }
    public function billplz_ipn()
    {
        $billplz = PaymentGatewayCredential::get_billplz_credential();
        $payment_data = $billplz->ipn_response();
        return $this->common_ipn_data($payment_data);
    }
    public function zitopay_ipn()
    {
        $zitopay = PaymentGatewayCredential::get_zitopay_credential();
        $payment_data = $zitopay->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function iyzipay_ipn()
    {
        $iyzipay = PaymentGatewayCredential::get_iyzipay_credential();
        $payment_data = $iyzipay->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    public function toyyibpay_ipn()
    {
        $toyyibpay = PaymentGatewayCredential::get_toyyibpay_credential();
        $payment_data = $toyyibpay->ipn_response();
        return $this->common_ipn_data($payment_data);
    }

    private function paystack_action($data)
    {
        if ($data['type'] === 'deposit')
        {
            return (new BuyerWalletPaymentController())->paystack_common_ipn_data($data);
        } else {
            return $this->common_ipn_data($data);
        }
    }

    private function common_ipn_data($payment_data)
    {
        if (isset($payment_data['status']) && $payment_data['status'] === 'complete') {
            try {
                $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
                $this->send_order_mail($payment_data['order_id']);
                $this->tenant_create_event_with_credential_mail($payment_data['order_id']);
                $this->update_tenant($payment_data);

            } catch (\Exception $exception) {
                $message = $exception->getMessage();
                if(str_contains($message,'Access denied')){
                    if(request()->ajax()){
                        abort(462,__('Database created failed, Make sure your database user has permission to create database'));
                    }
                }

                $payment_details = PaymentLogs::where('id',$payment_data['order_id'])->first();
                if(empty($payment_details))
                {
                    abort(500,__('Does not exist, Tenant does not exists'));
                }
                LandlordPricePlanAndTenantCreate::store_exception($payment_details->tenant_id,'Domain create',$exception->getMessage(), 0);

                //todo: send an email to admin that this user database could not able to create automatically

                try {
                    $message = sprintf(__('Database Creating failed for user id %1$s , please checkout admin panel and generate database for this user from admin panel manually'),
                        $payment_details->user_id);
                    $subject = sprintf(__('Database Crating failed for user id %1$s'),$payment_details->user_id);
                    Mail::to(get_static_option_central('site_global_email'))->send(new BasicMail($message,$subject));

                } catch (\Exception $e) {
                    LandlordPricePlanAndTenantCreate::store_exception($payment_details->tenant_id,'domain failed email',$e->getMessage(), 0);
                }
            }

            $order_id = wrap_random_number($payment_data['order_id']);
            return redirect()->route(self::SUCCESS_ROUTE, $order_id);
        }

        return $this->cancel_page();
    }

    private function update_database($order_id, $transaction_id)
    {
        PaymentLogs::where('id', $order_id)->update([
            'transaction_id' => $transaction_id,
            'status' => 'complete',
            'payment_status' => 'complete',
            'updated_at' => Carbon::now()
        ]);
    }

    public function update_tenant($payment_data)
    {
        try{
            $payment_log = PaymentLogs::where('id', $payment_data['order_id'])->first();
            $tenant = Tenant::find($payment_log->tenant_id);

            if ($payment_log->payment_status == 'complete')
            {
                if ($payment_log->is_renew == 1)
                {
                    \DB::table('tenants')->where('id', $tenant->id)->update([
                        'renew_status' => $renew_status = is_null($tenant->renew_status) ? 0 : $tenant->renew_status+1,
                        'is_renew' => $renew_status == 0 ? 0 : 1,
                        'start_date' => $payment_log->start_date,
                        'expire_date' => get_plan_left_days($payment_log->package_id, $tenant->expire_date)
                    ]);
                }

                (new SmsSendAction())->smsSender($tenant->user);
            }
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            if(str_contains($message,'Access denied')){
                abort(462,__('Database created failed, Make sure your database user has permission to create database'));
            }
        }

    }

    public function send_order_mail($order_id)
    {
        $package_details = PaymentLogs::where('id', $order_id)->first();
        $all_fields = [];
        unset($all_fields['package']);
        $all_attachment = [];
        $order_mail = get_static_option('order_page_form_mail') ? get_static_option('order_page_form_mail') : get_static_option('site_global_email');

        try {
            Mail::to($order_mail)->send(new PlaceOrder($all_fields, $all_attachment, $package_details, "admin", 'regular'));
            Mail::to($package_details->email)->send(new PlaceOrder($all_fields, $all_attachment, $package_details, 'user', 'regular'));

        } catch (\Exception $e) {
//            return redirect()->back()->with(['type' => 'danger', 'msg' => $e->getMessage()]);
        }
    }

    public function tenant_create_event_with_credential_mail($order_id)
    {
        $log = PaymentLogs::findOrFail($order_id);
        if (empty($log))
        {
            abort(462,__('Does not exist, Tenant does not exists'));
        }

        $user = User::where('id', $log->user_id)->first();
        $tenant = Tenant::find($log->tenant_id);

        if (!empty($log) && $log->payment_status == 'complete' && is_null($tenant)) {
//            event(new TenantRegisterEvent($user, $log->tenant_id, $log->theme_slug));
                Tenant::create(['id' => $log->tenant_id]);
            try {
                $raw_pass = get_static_option_central('tenant_admin_default_password') ??'12345678';
                $credential_password = $raw_pass;
                $credential_email = $user->email;
                $credential_username = get_static_option_central('tenant_admin_default_username') ?? 'super_admin';

                Mail::to($credential_email)->send(new TenantCredentialMail($credential_username, $credential_password));
            } catch (\Exception $e) {}

        } else if (!empty($log) && $log->payment_status == 'complete' && !is_null($tenant) && $log->is_renew == 0) {
            try {
                $raw_pass = get_static_option_central('tenant_admin_default_password') ?? '12345678';
                $credential_password = $raw_pass;
                $credential_email = $user->email;
                $credential_username = get_static_option_central('tenant_admin_default_username') ?? 'super_admin';

                Mail::to($credential_email)->send(new TenantCredentialMail($credential_username, $credential_password));
            } catch (\Exception $exception) {
                $message = $exception->getMessage();
                if(str_contains($message,'Access denied')){
                    abort(463,__('Database created failed, Make sure your database user has permission to create database'));
                }
            }
        }

        return true;
    }

    public function applyCoupon($code, $package_price)
    {
        if (empty($code))
        {
            return $package_price;
        }

        $coupon_info = Coupon::published()->active()->where('code', $code)
            ->select('code', 'discount_type', 'discount_amount')
            ->first();

        if ($coupon_info)
        {
            $coupon_info['discount_type'] = LandlordCouponType::getText($coupon_info->discount_type);

            $new_price = 0;
            if ($coupon_info->discount_type === 'percentage')
            {
                $percent_amount = ($coupon_info->discount_amount / 100) * $package_price;
                $new_price = $package_price - $percent_amount;
            } else {
                $new_price = $package_price - $coupon_info->discount_amount;
            }

            return $new_price;
        }

        return $package_price;
    }
}
