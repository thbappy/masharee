<?php

namespace Modules\ShippingPlugin\Http\Controllers;

use App\Helpers\FlashMsg;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ShippingPlugin\Entities\ShippingApiOrderStatus;
use Modules\ShippingPlugin\Http\Services\AuthorizationService;
use Modules\ShippingPlugin\Http\Services\Gateways\DHL;
use Modules\ShippingPlugin\Http\Services\ShippingService;
use Illuminate\Support\Facades\DB;
use App\Models\ProductOrder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
class ShippingPluginController extends Controller
{
    public function index()
    {
        $active_shipping_gateway = get_static_option('active_shipping_gateway');

        $has_orders = false;
        $orders = [];
        if (!empty($active_shipping_gateway))
        {
            $gateways = ShippingService::gateways();
            $has_orders = $gateways[$active_shipping_gateway]['api_order_flag'];

            $orders = ShippingApiOrderStatus::orderByDesc('id')->paginate(10);
        }

        return view('shippingplugin::backend.index', compact('orders', 'active_shipping_gateway', 'has_orders'));
    }

    public function track(Request $request)
    {
        if ($this->availability()['status'])
        {
            return $this->availability()['response'];
        }

        $validated = $request->validate([
            'tracking_number' => 'required|alpha_num'
        ]);

        $track_order = new ShippingService($validated['tracking_number']);
        $track_order = $track_order->track();

        return back()->with(FlashMsg::explain($track_order['status'] ? 'success' : 'danger', $track_order['title']))
            ->with('tracking_data', $track_order);
    }

    public function availability()
    {
        return [
            'status' => empty(get_static_option('active_shipping_gateway')),
            'response' => back()->with(FlashMsg::explain('danger', __('Service not available right now')))
        ];
    }

    public function settings()
    {
        $gateways = ShippingService::gateways();
        return view('shippingplugin::backend.settings', compact('gateways'));
    }

    public function UpdateSettings(Request $request)
    {
        $validated = $request->validate([
            'shipping_gateway_name' => 'required',
            'dhl_api_key' => 'required_if:shipping_gateway_name,dhl',
            'dhl_api_username' => 'required_if:shipping_gateway_name,dhl',
            'dhl_shipping_username' => 'required_if:shipping_gateway_name,dhl_shipping',
            'dhl_shipping_password' => 'required_if:shipping_gateway_name,dhl_shipping',
            'dhl_shipping_api_url' => 'required_if:shipping_gateway_name,dhl_shipping',
            'dhl_shipping_account_number' => 'required_if:shipping_gateway_name,dhl_shipping',

            'shiprocket_api_user_email' => 'required_if:shipping_gateway_name,shiprocket',
            'shiprocket_api_user_password' => 'required_if:shipping_gateway_name,shiprocket',
            'shiprocket_api_authorization_token' => 'nullable',
            
             // Aramex validation rules
            'aramex_shipping_username' => 'required_if:shipping_gateway_name,aramex_shipping',
            'aramex_shipping_password' => 'required_if:shipping_gateway_name,aramex_shipping',
            'aramex_shipping_account_number' => 'required_if:shipping_gateway_name,aramex_shipping',
            'aramex_account_pin' => 'required_if:shipping_gateway_name,aramex_shipping',
            'aramex_client_code' => 'required_if:shipping_gateway_name,aramex_shipping',
            'aramex_shipping_api_url' => 'required_if:shipping_gateway_name,aramex_shipping',
            
        ]);
        
      
        $shipping_gateway_name = $request->shipping_gateway_name;
        unset($request->shipping_gateway_name);
        unset($validated['shipping_gateway_name']);

        foreach ($validated ?? [] as $index => $item)
        {
            update_static_option($index, esc_html($item));
        }

        $gateway_name = $shipping_gateway_name.'_api_authorization_token';
        if(empty($request->$gateway_name))
        {

            (new AuthorizationService(esc_html($shipping_gateway_name)))->checkAuthorization()->saveAuthorization();
        }

        return back()->with(FlashMsg::settings_update('Shipping plugin settings updated'));
    }

    public function UpdateConfiguration(Request $request)
    {
        $rules = [
            'shipping_gateway_name' => 'required',
            'shiprocket_auto_create_order_option' => 'nullable',
            'shiprocket_order_tracking_option' => 'nullable',
            'shiprocket_pickup_location' => 'nullable',
                      'dhl_shipping_auto_create_order_option'=>'nullable',
            
            'dhl_shipping_order_tracking_option'=>'nullable',
            
            // Aramex options
            'aramex_shipping_auto_create_order_option' => 'nullable',
            'aramex_shipping_order_tracking_option' => 'nullable',
            'aramex_account_number' => 'nullable',
            'aramex_user_name' => 'nullable',
            'aramex_password' => 'nullable',
            'aramex_api_url' => 'nullable',
        ];

        $request->validate($rules);
        unset($request->shipping_gateway_name);
        unset($rules['shipping_gateway_name']);

        foreach ($rules ?? [] as $index => $item)
        {
            update_static_option($index, empty(esc_html($request->$index)) ? null : trim(esc_html($request->$index)));
        }

        return back()->with(FlashMsg::settings_update('Shipping plugin settings updated'));
    }

    public function changeStatus()
    {
        $request = \request();

        $this->validation($request);
           
    
           
        // if($request->option ==  'dhl_shipping'){

            
        //      $dhlshipping = get_static_option("dhl_shipping");
              
        //      if(!$dhlshipping){
               
        //         update_static_option("dhl_shipping" , 1);
        //      }else{
                 
        //          if($dhlshipping == 1){
        //             //   dd(0);
        //             update_static_option("dhl_shipping" , 0);
        //          }else{
        //             //   dd(1);
        //             update_static_option("dhl_shipping" , 1); 
        //          }
        //      }
        // }
        // elseif($request->option == 'aramex_shipping') {
        //     $aramexShipping = get_static_option("aramex_shipping");
        //         if($aramexShipping == 1) {
        //             // dd(0);
        //             update_static_option("aramex_shipping", 0);
        //         } else {
        //             // dd(1);
        //             update_static_option("aramex_shipping", 1);
        //         }
        // }
        if ($request->option == 'dhl_shipping') {
            $dhlshipping = get_static_option("dhl_shipping");
            logger()->info("DHL Shipping current value: " . $dhlshipping); // Debug log
        
            if (!$dhlshipping) {
                // logger()->info("Updating DHL Shipping to 1");
                update_static_option("dhl_shipping", 1);
            } else {
                if ($dhlshipping == 1) {
                    // logger()->info("Updating DHL Shipping to 0");
                    update_static_option("dhl_shipping", 0);
                } else {
                    // logger()->info("Updating DHL Shipping to 1");
                    update_static_option("dhl_shipping", 1);
                }
            }
        } elseif ($request->option == 'aramex_shipping') {
            $aramexShipping = get_static_option("aramex_shipping");
            logger()->info("Aramex Shipping current value: " . $aramexShipping); // Debug log
        
            if ($aramexShipping == 1) {
                logger()->info("Updating Aramex Shipping to 0");
                update_static_option("aramex_shipping", 0);
            } else {
                logger()->info("Updating Aramex Shipping to 1");
                update_static_option("aramex_shipping", 1);
            }
        }
        else{
            
             if (empty(get_static_option("active_shipping_gateway")))
            {
                
                update_static_option("active_shipping_gateway" , esc_html($request->option));
            } else {
                if (get_static_option('active_shipping_gateway') == esc_html($request->option))
                {
                    delete_static_option("active_shipping_gateway");
                } else {
                    delete_static_option("active_shipping_gateway");
                    update_static_option("active_shipping_gateway" , esc_html($request->option));
                }
            }
        }

        return response()->json([
            'type' => "success"
        ]);
    }

    private function validation($request)
    {
        abort_if(!$request->has('option'), 404);
        abort_if(empty($request->option), 404);

        $gateway_slugs_array = data_get(ShippingService::gateways(), '*.slug');
        abort_if(!in_array($request->option, $gateway_slugs_array), 404);
    }
}
