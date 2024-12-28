<?php

namespace Modules\ShippingPlugin\Observers;

use App\Models\ProductOrder;
use Modules\Product\Entities\ProductInventory;
use Modules\ShippingPlugin\Http\Services\ShippingService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderCreateObserver
{
    //old 
    // private object $order_details;
    
    
    //my code
    private  $order_details;


    public function created(ProductOrder $order)
    {
        if ($order->checkout_type === 'cod')
        {
            $this->order_details = $order;
            $this->shippingOrderCreate();
            
             Log::channel('dhl_shipping')->info('DHL API Response:');
        }
    }

    public function updated(ProductOrder $order)
    {
        
            // $this->order_details = $order;
            // $this->shippingOrderCreate();
          
        if ($order->checkout_type !== 'cod' && $order->payment_status === 'success')
        {
            $this->order_details = $order;
            $this->shippingOrderCreate();
            Log::channel('dhl_shipping')->info('DHL API Response: updated method');
        }
    }
    
    
    
     // My Code
    private function shippingOrderCreate()
    {
        // Check if the 'shipping_data' column exists in the 'product_orders' table and add it if missing
        if (!Schema::hasColumn('product_orders', 'shipping_data')) {
            DB::statement('ALTER TABLE product_orders ADD COLUMN shipping_data LONGTEXT NULL AFTER id');
        }

        // Check if DHL shipping is enabled
        if (get_static_option("dhl_shipping") == 1) {
            if (get_static_option("dhl_shipping_auto_create_order_option") == 'on') {
                $response = $this->storeDHLshipping(
                    get_static_option("dhl_pickup_location"),
                    $this->getCustomerDetails(),
                    $this->getOrderItems(),
                     Log::channel('dhl_shipping')->info('call to storeDHLshipping')
                    
                );
                $this->order_details->shipping_data = $response;
                $this->order_details->saveQuietly();
            }
        } else {

            // Check if the ShippingPlugin is available and active
            if (moduleExists('ShippingPlugin') && isPluginActive('ShippingPlugin')) {
                $active_gateway = get_static_option('active_shipping_gateway');
                
                if (!empty($active_gateway) && !empty(get_static_option("{$active_gateway}_auto_create_order_option"))) {
                    $order = $this->order_details;
                    $order_items = $this->getOrderItems();

                    // Determine payment type
                    $payment_type = $order->payment_status == 'success' ? 'Prepaid' : 'COD';

                    // Create shipping order with the appropriate service
                    $dhlData = ShippingService::createOrder([
                        "order_id" => $order->id,
                        "order_date" => $order->created_at->format('Y-m-d H:i'),
                        "pickup_location" => get_static_option("{$active_gateway}_pickup_location"),
                        "comment" => $order->message,
                        "billing_customer_name" => $order->name,
                        "billing_last_name" => "",
                        "billing_address" => $order->address,
                        "billing_city" => $order->getCity?->name,
                        "billing_pincode" => $order->zipcode,
                        "billing_state" => $order->getState?->name,
                        "billing_country" => $order->getCountry?->name,
                        "billing_email" => $order->email,
                        "billing_phone" => (int) $order->phone,
                        "shipping_is_billing" => true,
                        "order_items" => $order_items,
                        "payment_method" => $payment_type,
                        "shipping_charges" => json_decode($order->payment_meta)->shipping_cost ?? 0,
                        "giftwrap_charges" => 0,
                        "transaction_charges" => 0,
                        "total_discount" => $order->coupon_discounted,
                        "sub_total" => json_decode($order->payment_meta)->total,
                        "length" => 10,
                        "breadth" => 10,
                        "height" => 10,
                        "weight" => 10
                    ]);
                }

                Log::channel('dhl_shipping')->info('DHL API Response:', ['context' => 'shippingOrderCreate last line']);
            }
        }
    }

    private function getOrderItems()
    {
        $order = $this->order_details;
        $items = json_decode($order->order_details);
        $old_sku = '';
        $order_items = [];

        foreach ($items ?? [] as $item) {
            $sku = (ProductInventory::where('product_id', $item->id)->first())->sku ?? '';
            $variant_color = strtolower($item->options?->color_name ?? null);
            $variant_size = strtolower($item->options?->size_name ?? null);

            // Prepare each item for the shipping order
            $order_items[] = [
                "name" => $variant_color ? $item->name . " : $variant_color - $variant_size" : $item->name,
                "sku" => $old_sku == $sku ? $sku . "-$variant_color-$variant_size" : $sku,
                "units" => $item->qty,
                "selling_price" => $item->price,
                "tax" => ($item->price * $item->options->tax_options_sum_rate) / 100,
                "hsn" => ""
            ];

            $old_sku = $sku;
        }

        return $order_items;
    }

    private function getCustomerDetails()
    {
        $order = $this->order_details;
        return [
            "billing_customer_name" => $order->name,
            "billing_last_name" => "",
            "billing_address" => $order->address,
            "billing_city" => $order->getCity?->name,
            "billing_pincode" => $order->zipcode,
            "billing_state" => $order->getState?->name,
            "billing_country" => $order->getCountry?->name,
            "billing_email" => $order->email,
            "billing_phone" => (int) $order->phone
        ];
    }

    public function storeDHLshipping($pickupLocation, $customerDetails, $orderItems)
    {
        try {
            // $username = get_static_option("dhl_shipping_username");
            // $password = get_static_option("dhl_shipping_password");
             $username = 'apJ7lI8tZ7lB2m';
             $password = 'X#5nD#3lY$8tF^2u';
            $credentials = base64_encode($username . ':' . $password);
            // $authorizationHeader = 'Basic ' . $credentials;
              $authorizationHeader = 'Bearer ' . $credentials;
            // $dhlAPI = get_static_option("dhl_shipping_api_url");
            $dhlAPI = 'https://api-mock.dhl.com/mydhlapi/shipments';

            // Make API request to DHL
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'Authorization' => $authorizationHeader,
                'Content-Type' => 'application/json',
            ])->post("{$dhlAPI}/shipments", [
                'plannedShippingDateAndTime' => date('Y-m-d\TH:i:s\Z', strtotime('+2 days')),
                'pickup' => ['isRequested' => false],
                'productCode' => 'P',
                'customerDetails' => $customerDetails,
                'content' => [
                    'packages' => $orderItems,
                    'isCustomsDeclarable' => true,
                    'declaredValue' => 120,
                    'declaredValueCurrency' => 'USD',
                ],
                'pickupLocation' => $pickupLocation,
            ]);

            // Check if response was successful
            if ($response->successful()) {
                return $response->json();
                 Log::channel('dhl_shipping')->info('success storeDHLshipping DHL API Response:', $response->json());
            }
            Log::channel('dhl_shipping')->info('storeDHLshipping DHL API Response:', $response->json());
            return ['status' => false, 'message' => 'Failed to create DHL shipment.'];
        } catch (\Exception $ex) {
            Log::channel('dhl_shipping')->error('storeDHLshipping DHL API Error:', ['error' => $ex->getMessage()]);
            return ['status' => false, 'message' => $ex->getMessage()];
        }
    }

    
    
    
    //old code 

    // private function shippingOrderCreate()
    // {
    //     if (!Schema::hasColumn('product_orders', 'shipping_data')) {
    //         DB::statement('ALTER TABLE product_orders ADD COLUMN shipping_data LONGTEXT NULL AFTER id');
    //     } 
    //     if(get_static_option("dhl_shipping") == 1){

    //         if(get_static_option("dhl_shipping_auto_create_order_option") == 'on'){
    //             $response = $this->storeDHLshipping();
    //             $this->order_details->shipping_data = $response;;;; ;
    //             $this->order_details->saveQuietly();
    //         }
    //     }else{
    //         if ((moduleExists('ShippingPlugin') && isPluginActive('ShippingPlugin')))
    //         {
    //             $active_gateway = get_static_option('active_shipping_gateway');
    //             if (!empty($active_gateway) && !empty(get_static_option("{$active_gateway}_auto_create_order_option")))
    //             {
    //                 $order = $this->order_details;
    //                 $items = json_decode($order->order_details);
    
    //                 $old_sku = '';
    //                 $order_items = [];
    //                 foreach ($items ?? [] as $item)
    //                 {
    //                     $sku = (ProductInventory::where('product_id', $item->id)->first())->sku ?? '';
    //                     $variant_color = strtolower($item->options?->color_name ?? null);
    //                     $variant_size = strtolower($item->options?->size_name ?? null);
    
    //                     $order_items[] = [
    //                         "name" => $variant_color ? $item->name." : $variant_color - $variant_size" : $item->name,
    //                         "sku" => $old_sku == $sku ? $sku."-$variant_color-$variant_size" : $sku,
    //                         "units" => $item->qty,
    //                         "selling_price" => $item->price,
    //                         "tax" => ($item->price * $item->options->tax_options_sum_rate) / 100,
    //                         "hsn" => ""
    //                     ];
    
    //                     $old_sku = $sku;
    //                 }
    
    //                 $payment_type = 'COD';
    //                 if ($order->payment_status == 'success')
    //                 {
    //                     $payment_type = 'Prepaid';
    //                 }
    
    
    //                 ShippingService::createOrder([
    //                     "order_id" => $order->id,
    //                     "order_date" => $order->created_at->format('Y-m-d H:i'),
    //                     "pickup_location" => get_static_option("{$active_gateway}_pickup_location"),
    // //                    "channel_id" => "",
    //                     "comment" => $order->message, //"Reseller: M/s Goku",
    //                     "billing_customer_name" => $order->name, //"Naruto",
    //                     "billing_last_name" => "",
    //                     "billing_address" => $order->address, //"House 221B, Leaf Village",
    // //                    "billing_address_2" => "Near Hokage House",
    //                     "billing_city" => $order->getCity?->name, //"New Delhi",
    //                     "billing_pincode" => $order->zipcode,
    //                     "billing_state" => $order->getState?->name, //"Delhi",
    //                     "billing_country" => $order->getCountry?->name, //"India",
    //                     "billing_email" => $order->email, //"naruto@uzumaki.com",
    //                     "billing_phone" => (int) $order->phone, //"9876543210",
    //                     "shipping_is_billing" => true,
    //                     "order_items" => $order_items,
    //                     "payment_method" => $payment_type,
    //                     "shipping_charges" => json_decode($order->payment_meta)->shipping_cost ?? 0,
    //                     "giftwrap_charges" => 0,
    //                     "transaction_charges" => 0,
    //                     "total_discount" => $order->coupon_discounted,
    //                     "sub_total" => json_decode($order->payment_meta)->total,
    //                     "length" => 10,
    //                     "breadth" => 10,
    //                     "height" => 10,
    //                     "weight" => 10
    //                 ]);
    //             }
    //         }

    //     }

        
    // }

    // public function storeDHLshipping() {


    //      try {

    //           $username = get_static_option("dhl_shipping_username");
    //           $password = get_static_option("dhl_shipping_password");
            

            
    //          $credentials = base64_encode($username . ':' . $password);
            
    //          $authorizationHeader = 'Basic ' . $credentials;

    
    //         $arrayVar = [
    //             "plannedShippingDateAndTime" => "2022-10-19T19:19:40 GMT+00:00",
    //             "pickup" => ["isRequested" => false],
    //             "productCode" => "P",
    //             "accounts" => [["typeCode" => "shipper", "number" => get_static_option("dhl_shipping_account_number")]],
         
            
    //             "customerDetails" => [
    //                 "shipperDetails" => [
    //                     "postalAddress" => [
    //                         "postalCode" => "526238",
    //                         "cityName" => "Zhaoqing",
    //                         "countryCode" => "CN",
    //                         "addressLine1" =>
    //                             "4FENQU, 2HAOKU, WEIPINHUI WULIU YUAN，DAWANG",
    //                         "countyName" => "SIHUI",
    //                         "countryName" => "CHINA, PEOPLES REPUBLIC",
    //                     ],
    //                     "contactInformation" => [
    //                         "email" => "shipper_create_shipmentapi@dhltestmail.com",
    //                         "phone" => "18211309039",
    //                         "companyName" => "Cider BookStore",
    //                         "fullName" => "LiuWeiMing",
    //                     ],
            
    //                 ],
    //                 "receiverDetails" => [
    //                     "postalAddress" => [
    //                         "cityName" => "Graford",
    //                         "countryCode" => "US",
    //                         "postalCode" => "76449",
    //                         "addressLine1" => "116 Marine Dr",
    //                         "countryName" => "UNITED STATES OF AMERICA",
    //                     ],
    //                     "contactInformation" => [
    //                         "email" => "recipient_create_shipmentapi@dhltestmail.com",
    //                         "phone" => "9402825665",
    //                         "companyName" => "Baylee Marshall",
    //                         "fullName" => "Baylee Marshall",
    //                     ],
                       
                       
    //                 ],
    //             ],
    //             "content" => [
    //                 "packages" => [
    //                     [
    //                         "typeCode" => "2BP",
    //                         "weight" => 0.5,
    //                         "dimensions" => ["length" => 1, "width" => 1, "height" => 1],
    //                         "customerReferences" => [
    //                             ["value" => "3654673", "typeCode" => "CU"],
    //                         ],
    //                         "description" => "Piece content description",
    //                         "labelDescription" => "bespoke label description",
    //                     ],
    //                 ],
    //                 "isCustomsDeclarable" => true,
    //                 "declaredValue" => 120,
    //                 "declaredValueCurrency" => "USD",
    //                 "exportDeclaration" => [
    //                     "lineItems" => [
    //                         [
    //                             "number" => 1,
    //                             "description" => "Harry Steward biography first edition",
    //                             "price" => 15,
    //                             "quantity" => ["value" => 4, "unitOfMeasurement" => "GM"],
    //                             "commodityCodes" => [
    //                                 ["typeCode" => "outbound", "value" => "84713000"],
    //                                 ["typeCode" => "inbound", "value" => "5109101110"],
    //                             ],
    //                             "exportReasonType" => "permanent",
    //                             "manufacturerCountry" => "US",
    //                             "exportControlClassificationNumber" => "US123456789",
    //                             "weight" => ["netValue" => 0.1, "grossValue" => 0.7],
    //                             "isTaxesPaid" => true,
    //                             "additionalInformation" => ["450pages"],
    //                             "customerReferences" => [
    //                                 ["typeCode" => "AFE", "value" => "1299210"],
    //                             ],
    //                             "customsDocuments" => [
    //                                 [
    //                                     "typeCode" => "COO",
    //                                     "value" => "MyDHLAPI - LN#1-CUSDOC-001",
    //                                 ],
    //                             ],
    //                         ],
    //                         [
    //                             "number" => 2,
    //                             "description" => "Andromeda Chapter 394 - Revenge of Brook",
    //                             "price" => 15,
    //                             "quantity" => ["value" => 4, "unitOfMeasurement" => "GM"],
    //                             "commodityCodes" => [
    //                                 ["typeCode" => "outbound", "value" => "6109100011"],
    //                                 ["typeCode" => "inbound", "value" => "5109101111"],
    //                             ],
    //                             "exportReasonType" => "permanent",
    //                             "manufacturerCountry" => "US",
    //                             "exportControlClassificationNumber" => "US123456789",
    //                             "weight" => ["netValue" => 0.1, "grossValue" => 0.7],
    //                             "isTaxesPaid" => true,
    //                             "additionalInformation" => ["36pages"],
    //                             "customerReferences" => [
    //                                 ["typeCode" => "AFE", "value" => "1299211"],
    //                             ],
    //                             "customsDocuments" => [
    //                                 [
    //                                     "typeCode" => "COO",
    //                                     "value" => "MyDHLAPI - LN#1-CUSDOC-001",
    //                                 ],
    //                             ],
    //                         ],
    //                     ],
    //                     "invoice" => [
    //                         "number" => "2667168671",
    //                         "date" => "2022-10-22",
    //                         "instructions" => ["Handle with care"],
    //                         "totalNetWeight" => 0.4,
    //                         "totalGrossWeight" => 0.5,
    //                         "customerReferences" => [
    //                             ["typeCode" => "UCN", "value" => "UCN-783974937"],
    //                             ["typeCode" => "CN", "value" => "CUN-76498376498"],
    //                             ["typeCode" => "RMA", "value" => "MyDHLAPI-TESTREF-001"],
    //                         ],
    //                         "termsOfPayment" => "100 days",
    //                         "indicativeCustomsValues" => [
    //                             "importCustomsDutyValue" => 150.57,
    //                             "importTaxesValue" => 49.43,
    //                         ],
    //                     ],
    //                     "remarks" => [["value" => "Right side up only"]],
    //                     "additionalCharges" => [
    //                         ["value" => 10, "caption" => "fee", "typeCode" => "freight"],
    //                         [
    //                             "value" => 20,
    //                             "caption" => "freight charges",
    //                             "typeCode" => "other",
    //                         ],
    //                         [
    //                             "value" => 10,
    //                             "caption" => "ins charges",
    //                             "typeCode" => "insurance",
    //                         ],
    //                         [
    //                             "value" => 7,
    //                             "caption" => "rev charges",
    //                             "typeCode" => "reverse_charge",
    //                         ],
    //                     ],
    //                     "destinationPortName" => "New York Port",
    //                     "placeOfIncoterm" => "ShenZhen Port",
    //                     "payerVATNumber" => "12345ED",
    //                     "recipientReference" => "01291344",
    //                     "exporter" => ["id" => "121233", "code" => "S"],
    //                     "packageMarks" => "Fragile glass bottle",
    //                     "declarationNotes" => [
    //                         ["value" => "up to three declaration notes"],
    //                     ],
    //                     "exportReference" => "export reference",
    //                     "exportReason" => "export reason",
    //                     "exportReasonType" => "permanent",
    //                     "licenses" => [["typeCode" => "export", "value" => "123127233"]],
    //                     "shipmentType" => "personal",
    //                     "customsDocuments" => [
    //                         ["typeCode" => "INV", "value" => "MyDHLAPI - CUSDOC-001"],
    //                     ],
    //                 ],
    //                 "description" => "Shipment",
    //                 "USFilingTypeValue" => "12345",
    //                 "incoterm" => "DAP",
    //                 "unitOfMeasurement" => "metric",
    //             ],
            
    //             "getTransliteratedResponse" => false,
    //             "estimatedDeliveryDate" => ["isRequested" => true, "typeCode" => "QDDC"],
             
    //         ];
    //         $response = Http::withHeaders([
    //             'Accept' => 'application/json',
    //             'Message-Reference' => 'd0e7832e-5c98-11ea-bc55-0242ac13',
    //             'Message-Reference-Date' => 'Wed, 21 Oct 2015 07:28:00 GMT',
    //             'Plugin-Name' => '',
    //             'Plugin-Version' => '',
    //             'Shipping-System-Platform-Name' => '',
    //             'Shipping-System-Platform-Version' => '',
    //             'Webstore-Platform-Name' => '',
    //             'Webstore-Platform-Version' => '',
    //           'Authorization' =>  $authorizationHeader, // Base64 encoded 'demo-key:demo-secret'
    //         ])->post('https://api-mock.dhl.com/mydhlapi/shipments',$arrayVar);
            
            
    //          $dhlAPI = get_static_option("dhl_shipping_api_url");
             

            
    //                 $response = Http::withHeaders([
    //                             'accept' => 'application/json',
    //                             'Message-Reference' => 'd0e7832e-5c98-11ea-bc55-0242ac13',
    //                             'Message-Reference-Date' => 'Wed, 21 Oct 2015 07:28:00 GMT',
    //                             'Plugin-Name' => '',
    //                             'Plugin-Version' => '',
    //                             'Shipping-System-Platform-Name' => '',
    //                             'Shipping-System-Platform-Version' => '',
    //                             'Webstore-Platform-Name' => '',
    //                             'Webstore-Platform-Version' => '',
    //                             'Authorization' => $authorizationHeader,
    //                             'Content-Type' => 'application/json',
    //               ])->post('https://express.api.dhl.com/mydhlapi/test/shipments?strictValidation=false&bypassPLTError=false&validateDataOnly=false', [
    //                     'plannedShippingDateAndTime' => date('Y-m-d\TH:i:s \G\M\TP', strtotime('+2 days')),
    //                     'pickup' => ['isRequested' => false],
    //                     'productCode' => 'P',
    //                     'localProductCode' => 'P',
    //                     'getRateEstimates' => false,
    //                     'accounts' => [
    //                         [
    //                             'typeCode' => 'shipper',
    //                             'number' => get_static_option("dhl_shipping_account_number"),
    //                         ],
    //                     ],
    //                     'valueAddedServices' => [
    //                         [
    //                             'serviceCode' => 'II',
    //                             'value' => 10,
    //                             'currency' => 'USD',
    //                         ],
    //                     ],
       
    //                     'customerDetails' => [
    //                         'shipperDetails' => [
    //                             'postalAddress' => [
    //                                 'postalCode' => '526238',
    //                                 'cityName' => 'Zhaoqing',
    //                                 'countryCode' => 'CN',
    //                                 'addressLine1' => '4FENQU, 2HAOKU, WEIPINHUI WULIU YUAN，DAWANG',
    //                                 'addressLine2' => 'GAOXIN QU, BEIJIANG DADAO, SIHUI,',
    //                                 'addressLine3' => 'ZHAOQING, GUANDONG',
    //                                 'countyName' => 'SIHUI',
    //                                 'countryName' => 'CHINA, PEOPLES REPUBLIC',
    //                             ],
    //                             'contactInformation' => [
    //                                 'email' => 'shipper_create_shipmentapi@dhltestmail.com',
    //                                 'phone' => '18211309039',
    //                                 'mobilePhone' => '18211309039',
    //                                 'companyName' => 'Cider BookStore',
    //                                 'fullName' => 'LiuWeiMing',
    //                             ],
    //                             'registrationNumbers' => [
    //                                 [
    //                                     'typeCode' => 'SDT',
    //                                     'number' => 'CN123456789',
    //                                     'issuerCountryCode' => 'CN',
    //                                 ],
    //                             ],
    //                             'bankDetails' => [
    //                                 [
    //                                     'name' => 'Bank of China',
    //                                     'settlementLocalCurrency' => 'RMB',
    //                                     'settlementForeignCurrency' => 'USD',
    //                                 ],
    //                             ],
    //                             'typeCode' => 'business',
    //                         ],
    //                         'receiverDetails' => [
    //                             'postalAddress' => [
    //                                 'cityName' => 'Graford',
    //                                 'countryCode' => 'US',
    //                                 'postalCode' => '76449',
    //                                 'addressLine1' => '116 Marine Dr',
    //                                 'countryName' => 'UNITED STATES OF AMERICA',
    //                             ],
    //                             'contactInformation' => [
    //                                 'email' => 'recipient_create_shipmentapi@dhltestmail.com',
    //                                 'phone' => '9402825665',
    //                                 'mobilePhone' => '9402825666',
    //                                 'companyName' => 'Baylee Marshall',
    //                                 'fullName' => 'Baylee Marshall',
    //                             ],
    //                             'registrationNumbers' => [
    //                                 [
    //                                     'typeCode' => 'SSN',
    //                                     'number' => 'US123456789',
    //                                     'issuerCountryCode' => 'US',
    //                                 ],
    //                             ],
    //                             'bankDetails' => [
    //                                 [
    //                                     'name' => 'Bank of America',
    //                                     'settlementLocalCurrency' => 'USD',
    //                                     'settlementForeignCurrency' => 'USD',
    //                                 ],
    //                             ],
    //                             'typeCode' => 'business',
    //                         ],
    //                     ],
    //                     'content' => [
    //                         'packages' => [
    //                             [
    //                                 'typeCode' => '2BP',
    //                                 'weight' => 0.5,
    //                                 'dimensions' => [
    //                                     'length' => 1,
    //                                     'width' => 1,
    //                                     'height' => 1,
    //                                 ],
    //                                 'customerReferences' => [
    //                                     [
    //                                         'value' => '3654673',
    //                                         'typeCode' => 'CU',
    //                                     ],
    //                                 ],
    //                                 'description' => 'Piece content description',
    //                                 'labelDescription' => 'bespoke label description',
                           
    //                             ],
    //                         ],
    //                         'isCustomsDeclarable' => true,
    //                         'declaredValue' => 120,
    //                         'declaredValueCurrency' => 'USD',
    //                         'exportDeclaration' => [
    //                             'lineItems' => [
    //                                 [
    //                                     'number' => 1,
    //                                     'description' => 'Harry Steward biography first edition',
    //                                     'price' => 15,
    //                                     'quantity' => [
    //                                         'value' => 4,
    //                                         'unitOfMeasurement' => 'GM',
    //                                     ],
    //                                     'commodityCodes' => [
    //                                         [
    //                                             'typeCode' => 'outbound',
    //                                             'value' => '84713000',
    //                                         ],
    //                                         [
    //                                             'typeCode' => 'inbound',
    //                                             'value' => '5109101110',
    //                                         ],
    //                                     ],
    //                                     'exportReasonType' => 'permanent',
    //                                     'manufacturerCountry' => 'US',
    //                                     'exportControlClassificationNumber' => 'US123456789',
    //                                     'weight' => [
    //                                         'netValue' => 0.1,
    //                                         'grossValue' => 0.7,
    //                                     ],
    //                                     'isTaxesPaid' => true,
    //                                     'additionalInformation' => [
    //                                         '450pages',
    //                                     ],
                                      
    //                                 ],
    //                                 [
    //                                     'number' => 2,
    //                                     'description' => 'Andromeda Chapter 394 - Revenge of Brook',
    //                                     'price' => 15,
    //                                     'quantity' => [
    //                                         'value' => 4,
    //                                         'unitOfMeasurement' => 'GM',
    //                                     ],
    //                                     'commodityCodes' => [
    //                                         [
    //                                             'typeCode' => 'outbound',
    //                                             'value' => '6109100011',
    //                                         ],
    //                                         [
    //                                             'typeCode' => 'inbound',
    //                                             'value' => '5109101111',
    //                                         ],
    //                                     ],
    //                                     'exportReasonType' => 'permanent',
    //                                     'manufacturerCountry' => 'US',
    //                                     'exportControlClassificationNumber' => 'US123456789',
    //                                     'weight' => [
    //                                         'netValue' => 0.1,
    //                                         'grossValue' => 0.7,
    //                                     ],
    //                                     'isTaxesPaid' => true,
    //                                     'additionalInformation' => [
    //                                         '36pages',
    //                                     ],
                                       
    //                                 ],
    //                             ],
    //                             'invoice' => [
    //                                 'number' => '2667168671',
    //                                 'date' => '2022-10-22',
    //                                 'instructions' => [
    //                                     'Handle with care',
    //                                 ],
    //                                 'totalNetWeight' => 0.4,
    //                                 'totalGrossWeight' => 0.5,
    //                                 'customerReferences' => [
    //                                     [
    //                                         'typeCode' => 'CU',
    //                                         'value' => '4655132747',
    //                                     ],
    //                                 ],
    //                             ],
    //                         ],
    //                         'unitOfMeasurement' => 'metric',
    //                         'incoterm' => 'DAP',
    //                         'description' => 'Piece content description',
    //                     ],
    //             ]);
    
    
    
    

      
          
    //       if($response->successful()){

    //           return $response->json();
    //       }
           
    //       return null;
      
    
    //      } catch (\Exception $ex) {
            
          
            
    //         return [
    //             'status' => false,
    //             'message' => $ex->getMessage()
    //         ];
    //      }


    // }
}


