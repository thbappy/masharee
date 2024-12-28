<?php

namespace App\Http\Controllers\Tenant\Frontend;

use App\Actions\Tenant\ZipFileDownloader;
use App\Enums\ProductTypeEnum;
use App\Events\SupportMessage;
use App\Helpers\LanguageHelper;
use App\Helpers\ResponseMessage;
use App\Helpers\SanitizeInput;
use App\Http\Controllers\Controller;
use App\Mail\BasicMail;
use App\Models\Newsletter;
use App\Models\Order;
use App\Models\OrderProducts;
use App\Models\Page;
use App\Models\PaymentLogs;
use App\Models\PricePlan;
use App\Models\ProductOrder;
use App\Models\SupportDepartment;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserDeliveryAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Blog\Entities\Blog;
use Barryvdh\DomPDF\Facade as PDF;
use Modules\CountryManage\Entities\City;
use Modules\CountryManage\Entities\Country;
use Modules\CountryManage\Entities\State;
use Modules\DigitalProduct\Entities\DigitalProduct;
use Modules\DigitalProduct\Entities\DigitalProductDownload;
use Modules\Product\Entities\ProductInventory;
use Modules\Product\Entities\ProductInventoryDetail;
use Modules\Product\Entities\ProductSellInfo;
use Modules\RefundModule\Entities\RefundProduct;
use Modules\TaxModule\Entities\CountryTax;
use Modules\TaxModule\Entities\StateTax;
use Modules\Wallet\Entities\Wallet;


class UserDashboardController extends Controller
{
    const BASE_PATH = 'tenant.frontend.user.dashboard.';

    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function user_index(){
        $package_orders = ProductOrder::where('user_id',$this->logged_user_details()->id)->count();
        $order_purchase = ProductOrder::where('user_id',$this->logged_user_details()->id)->sum('total_amount');

        $product_refunds = RefundProduct::where(['user_id' => $this->logged_user_details()->id, 'status' => 1])->count();
        $support_tickets = SupportTicket::where('user_id',$this->logged_user_details()->id)->count();

        $recent_logs = PaymentLogs::where('user_id',$this->logged_user_details()->id)->orderBy('id','desc')->take(4)->get();
        return view(self::BASE_PATH.'user-home')->with(
            [
                'package_orders' => $package_orders,
                'order_purchase' => $order_purchase,
                'product_refunds' => $product_refunds,
                'support_tickets' => $support_tickets,
                'recent_logs' => $recent_logs,
            ]
        );
    }

    public function user_email_verify_index(){
        $user_details = Auth::guard('web')->user();
        if ($user_details->email_verified == 1){
            return redirect()->route('user.home');
        }
        if (is_null($user_details->email_verify_token)){
            Tenant::find($user_details->id)->update(['email_verify_token' => Str::random(20)]);
            $user_details = Tenant::find($user_details->id);

            $message_body = __('Here is your verification code : ').' <span class="verify-code"> <b>'.$user_details->email_verify_token.'</b></span>';

            try{
                Mail::to($user_details->email)->send(new BasicMail([
                    'subject' => __('Verify your email address'),
                    'message' => $message_body
                ]));
            }catch(\Exception $e){
                //hanle error
            }
        }
        return view('tenant.frontend.user.email-verify');
    }

    public function reset_user_email_verify_code(){
        $user_details = Auth::guard('web')->user();
        if ($user_details->email_verified == 1){
            return redirect()->route('user.home');
        }

        $message_body = __('Here is your verification code : ').' <span class="verify-code">'.$user_details->email_verify_token.'</span>';
        try{

        }catch(\Exception $e){
            Mail::to($user_details->email)->send(new BasicMail([
                'subject' => __('Verify your email address'),
                'message' => $message_body
            ]));
        }

        return redirect()->route('user.email.verify')->with(['msg' => __('Resend Verify Email Success'),'type' => 'success']);
    }

    public function user_email_verify(Request $request){
        $this->validate($request,[
            'verification_code' => 'required'
        ],[
            'verification_code.required' => __('verify code is required')
        ]);
        $user_details = Auth::guard('web')->user();
        $user_info = Tenant::where(['id' =>$user_details->id,'email_verify_token' => $request->verification_code])->first();
        if (empty($user_info)){
            return redirect()->back()->with(['msg' => __('your verification code is wrong, try again'),'type' => 'danger']);
        }
        $user_info->email_verified = 1;
        $user_info->save();
        return redirect()->route('user.home');
    }

    public function user_profile_update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|numeric|users,mobile',
            'country' => 'required',
            'state' => 'nullable',
            'city' => 'nullable',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:191',
            'image' => 'nullable|string',
        ],[
            'name.' => __('name is required'),
            'email.required' => __('email is required'),
            'email.email' => __('provide valid email'),
        ]);

        $user = Auth::guard('web')->user();
        User::find($user->id)->update([
                'name' => esc_html($request->name),
                'email' => esc_html($request->email),
                'mobile' => esc_html($request->phone),
                'company' => esc_html($request->company),
                'address' => esc_html($request->address),
                'state' => esc_html($request->state),
                'city' => esc_html($request->city),
                'country' => esc_html($request->country),
                'postal_code' => esc_html($request->postal_code),
                'image' => esc_html($request->image)
            ]);

        return response()->json(['msg' => __('Profile update success'), 'type' => 'success']);
    }

    public function user_password_change(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ],
            [
                'old_password.required' => __('Old password is required'),
                'password.required' => __('Password is required'),
                'password.confirmed' => __('password must have be confirmed')
            ]
        );

        $user = User::findOrFail(Auth::guard('web')->user()->id);

        if (Hash::check($request->old_password, $user->password)) {

            $user->password = Hash::make($request->password);
            $user->save();
            Auth::guard('web')->logout();

            return response()->json([
                'msg' => __('Password Changed Successfully'),
                'type' => 'success',
                'url' => route('tenant.user.login')
            ]);
        }

        return response()->json(['msg' => __('Somethings Going Wrong! Please Try Again or Check Your Old Password'), 'type' => 'danger']);
    }



    public function logged_user_details(){
        $old_details = '';
        if (empty($old_details)){
            $old_details = User::findOrFail(Auth::guard('web')->user()->id);
        }
        return $old_details;
    }

    public function manage_account()
    {
        $user_details = Auth::guard('web')->user();
        $countries = Country::published()->select('id', 'name')->get();
        $states = State::where(['status' => 'publish', 'country_id' => $user_details->country])->select('id', 'name')->get();
        $cities = City::where(['status' => 'publish', 'state_id' => $user_details->state])->select('id', 'name')->get();

        return view(self::BASE_PATH.'manage-account', compact([
            'user_details',
            'countries',
            'states',
            'cities'
        ]));
    }

    public function edit_profile()
    {
        return view(self::BASE_PATH.'edit-profile')->with(['user_details' => $this->logged_user_details()]);
    }

    public function address_book(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|unique:user_delivery_addresses,phone',
            'country' => 'required',
            'state' => 'nullable',
            'city' => 'required',
            'postal_code' => 'required',
            'address' => 'required'
        ]);

        $user = $this->logged_user_details();

        $delivery_details = UserDeliveryAddress::updateOrCreate(
            [
                'user_id' => $user->id
            ],
            [
                'user_id' => $user->id,
                'country_id' => $request->country,
                'state_id' => $request->state,
                'city' => $request->city,
                'full_name' => SanitizeInput::esc_html($request->name),
                'phone' => SanitizeInput::esc_html($request->phone),
                'email' => SanitizeInput::esc_html(strtolower($request->email)),
                'postal_code' => SanitizeInput::esc_html($request->postal_code),
                'address' => SanitizeInput::esc_html($request->address)
            ]
        );

        return response()->json([
            'type' => 'success',
            'msg' => 'Your delivery address is updated',
        ]);
    }

    public function change_password()
    {
        return view(self::BASE_PATH.'change-password');
    }

    public function support_tickets(){
        $all_tickets = SupportTicket::where('user_id',$this->logged_user_details()->id)->paginate(10);
        $departments = SupportDepartment::where('status', 1)->get();

        return view('tenant.frontend.user.dashboard.support-tickets')->with([ 'all_tickets' => $all_tickets, 'departments' => $departments]);
    }

    public function support_ticket_priority_change(Request $request){
        $this->validate($request,[
            'priority' => 'required|string|max:191'
        ]);
        SupportTicket::findOrFail($request->id)->update([
            'priority' => $request->priority,
        ]);
        return 'ok';
    }

    public function support_ticket_status_change(Request $request){
        $this->validate($request,[
            'status' => 'required|string|max:191'
        ]);
        SupportTicket::findOrFail($request->id)->update([
            'status' => $request->status,
        ]);
        return 'ok';
    }
    public function support_ticket_view(Request $request,$id){
        $ticket_details = SupportTicket::findOrFail($id);
        $all_messages = SupportTicketMessage::where(['support_ticket_id'=>$id])->get();
        $q = $request->q ?? '';
        return view(self::BASE_PATH.'view-ticket')->with(['ticket_details' => $ticket_details,'all_messages' => $all_messages,'q' => $q]);
    }

    public function support_ticket_message(Request $request){
        $this->validate($request,[
            'ticket_id' => 'required',
            'user_type' => 'required|string|max:191',
            'message' => 'required',
            'send_notify_mail' => 'nullable|string',
            'file' => 'nullable|mimes:zip',
        ]);

        $ticket_info = SupportTicketMessage::create([
            'support_ticket_id' => $request->ticket_id,
            'user_id' => Auth::guard('web')->id(),
            'type' => $request->user_type,
            'message' => str_replace('script','',$request->message),
            'notify' => $request->send_notify_mail ? 'on' : 'off',
        ]);

        if ($request->hasFile('file')){
            $uploaded_file = $request->file;
            $file_extension = $uploaded_file->getClientOriginalExtension();
            $file_name =  pathinfo($uploaded_file->getClientOriginalName(),PATHINFO_FILENAME).time().'.'.$file_extension;
            $uploaded_file->move('assets/uploads/ticket',$file_name);
            $ticket_info->attachment = $file_name;
            $ticket_info->save();
        }

        //send mail to user
        event(new SupportMessage($ticket_info));

        return redirect()->back()->with(['msg' => __('Mail Send Success'), 'type' => 'success']);
    }

    public function package_orders(){
        $order_list = ProductSellInfo::where("user_id", \auth("web")->user()->id)->latest()->get();

        return view(self::BASE_PATH.'package-order', compact("order_list"));
    }

    public function order_list($id = null){

        if (!empty($id)){
            $order = ProductOrder::when(!empty($id), function ($query) use ($id) {
                $query->with("shipping");
                $query->where("id",$id);
            })->where("user_id", \auth("web")->user()->id)
                ->latest()->firstOrFail();

            return view(self::BASE_PATH.'order-details', compact("order"));
        }



        $order_list = ProductOrder::where("user_id", \auth("web")->user()->id)->latest()->paginate(10);

        return view(self::BASE_PATH.'order-list', compact("order_list"));
    }

    public function cancel_order(Request $request)
    {
        $this->validate($request, [
            'order_id' => 'required|string|max:191'
        ]);


        $order_details = ProductOrder::find($request->order_id);
        abort_if(Auth::guard('web')->user()->id !== $order_details->user_id, 403);

        $order_details->status = 'cancel';
        $order_details->save();

        if ($order_details->status === 'cancel') {
            $this->undostock($order_details);
        }

        foreach (json_decode($order_details->order_details) as $item)
        {
            if ($item->options->type == ProductTypeEnum::DIGITAL)
            {
                $product_download = DigitalProductDownload::where('product_id', $item->id)->first();
                if (!empty($product_download))
                {
                    $product_download->increment('download_count', 1);
                } else {
                    DigitalProductDownload::create([
                        'product_id' => $item->id,
                        'user_id' => $order_details->user_id ?? null,
                        'download_count' => 1
                    ]);
                }
            }
        }

        $data['subject'] = __('Your order status has been changed');
        $data['message'] = __('Hello') . ' ' . $order_details->name . '<br>';
        $data['message'] .= __('Your order') . ' #' . $order_details->id . ' ';
        $data['message'] .= __('status has been changed to') . ' ' . str_replace('_', ' ', $request->order_status) . '.';

        //send mail while order status change
        try {
            Mail::to($order_details->email)->send(new BasicMail($data['message'], $data['subject']));

        } catch (\Exception $e) {

            return redirect()->back()->with(['type' => 'danger', 'msg' => $e->getMessage()]);
        }


        return redirect()->back()->with(['msg' => __('The order is cancelled'), 'type' => 'success']);
    }

    private function undostock($order)
    {
        $ordered_products = OrderProducts::where('order_id', $order->id)->get();
        foreach ($ordered_products ?? [] as $product) {
            if ($product->variant_id !== null) {
                $variants = ProductInventoryDetail::where(['product_id' => $product->product_id, 'id' => $product->variant_id])->get();
                if (!empty($variants)) {
                    foreach ($variants ?? [] as $variant) {
                        $variant->increment('stock_count', $product->quantity);
                        $variant->decrement('sold_count', $product->quantity);
                    }
                }
            }

            $product_inventory = ProductInventory::where('product_id', $product->product_id ?? 0)->first();
            $product_inventory->increment('stock_count', $product->quantity ?? 0);
            $product_inventory->sold_count = $product_inventory->sold_count == null ? 1 : $product_inventory->sold_count - $product->quantity;
            $product_inventory->save();
        }
    }

    public function download_list($id = null){
        if (!empty($id)){
            $order = ProductOrder::when(!empty($id), function ($query) use ($id) {
                $query->with("shipping");
                $query->where("id",$id);
            })->where("user_id", \auth("web")->user()->id)
                ->latest()->firstOrFail();

            return view(self::BASE_PATH.'order-details', compact("order"));
        }


        $download_list = DigitalProductDownload::where("user_id", \auth("web")->user()->id)->latest()->paginate(10);

        return view(self::BASE_PATH.'download-list', compact("download_list"));
    }

    public function download($slug)
    {
        abort_if(empty($slug), 403);

        $user = Auth::guard('web')->user();

        $product = DigitalProduct::where('slug', $slug)->firstOrFail();
        $purchase_check = DigitalProductDownload::where(['product_id' => $product->id, 'user_id' => $user->id])->first();

        if (empty($purchase_check))
        {
            return redirect()->back()->with(['msg' => __('You need to purchase the product first'), 'type' => 'warning']);
        }

        $zipDownloader = new ZipFileDownloader();
        return $zipDownloader->download($product);

//        $zip_file_name = time().'.zip';
//        $zip_file_location = global_assets_path('assets/tenant/uploads/digital-product-file/'.tenant()->id.'/'.$zip_file_name);
//
//        $zip = new \ZipArchive();
//
//        if ($zip->open($zip_file_location, \ZipArchive::CREATE) === TRUE)
//        {
//            $real_file_path = global_assets_path('assets/tenant/uploads/digital-product-file/'.tenant()->id.'/'.$product->file);
//            $zip->addFile($real_file_path, $product->file);
//            $zip->close();
//        }
//
//        return response()->download($zip_file_location);


//        $headers = ['Content-Type: file/zip'];
//        if (file_exists($file_path)) {
//            return \Response::download($file_path, $file_name, $headers);
//        } else {
//            echo('File not found.');
//        }
    }

    public function generate_package_invoice(Request $request)
    {
        $payment_details = PaymentLogs::find($request->id);
        if (empty($payment_details)) {
            return abort(404);
        }

        $pdf = PDF::loadview('tenant.frontend.invoice.package-order', ['payment_details' => $payment_details])->setOptions(['defaultFont' => 'sans-serif']);
        return $pdf->download('package-invoice.pdf');
    }



    public function order_details($id)
    {

        $order_details = Order::find($id);
        if(empty($order_details)){
            abort(404);
        }
        $package_details = PricePlan::find($order_details->package_id);
        $payment_details = PaymentLogs::where('order_id', $id)->first();
        return themeView('pages.package.view-order')->with(
            [
                'order_details' => $order_details,
                'package_details' => $package_details,
                'payment_details' => $payment_details,
            ]
        );
    }

    public function package_order_cancel(Request $request){
        $this->validate($request,[
            'order_id' => 'required'
        ]);

        $order_details = PaymentLogs::where(['id' => $request->order_id,'user_id' => Auth::guard('web')->user()->id])->first();

        //send mail to admin
        $order_page_form_mail =  get_static_option('order_page_form_mail');
        $order_mail = $order_page_form_mail ? $order_page_form_mail : get_static_option('site_global_email');
        $order_details->status = 'cancel';
        $order_details->save();
        //send mail to customer
        $data['subject'] = __('one of your package order has been cancelled');
        $data['message'] = __('hello').'<br>';
        $data['message'] .= __('your package order ').' #'.$order_details->id.' ';
        $data['message'] .= __('has been cancelled by the user');

        //send mail while order status change
        try {
            Mail::to($order_mail)->send(new BasicMail($data['message'],$data['subject']));
        }catch (\Exception $e){
            //handle error
            return redirect()->back()->with(['msg' => __('Order Cancel, mail send failed'), 'type' => 'warning']);
        }
        if (!empty($order_details)){
            //send mail to customer
            $data['subject'] = __('your order status has been cancel');
            $data['message'] = __('hello'). '<br>';
            $data['message'] .= __('your order').' #'.$order_details->id.' ';
            $data['message'] .= __('status has been changed to cancel');
            try {
                //send mail while order status change
                Mail::to($order_details->email)->send(new BasicMail($data['message'], $data['subject']));
            }catch (\Exception $e){
                //handle error
                return redirect()->back()->with(['msg' => __('Order Cancel, mail send failed'), 'type' => 'warning']);
            }

        }
        return redirect()->back()->with(['msg' => __('Order Cancel'), 'type' => 'warning']);
    }
}
