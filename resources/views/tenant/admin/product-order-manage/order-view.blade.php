@extends(route_prefix().'admin.admin-master')
@section('title')
    {{__('Order Details')}}
@endsection
@section('style')
    <x-media-upload.css/>
@endsection
@section('title')
    {{__('Order Details')}}
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row g-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">{{__('Order Details')}}</h4>
                        <x-link-with-popover url="{{route(route_prefix().'admin.product.order.manage.all')}}"
                                             class="info">{{__('All Orders')}}</x-link-with-popover>

                        @php
                            $order_meta = json_decode($order->payment_meta);
                            
                    
                        @endphp

                        <!-- Order status start-->
                        <div class="order-status-wrap order-details-page">
                            <table class="order-status-inner">
                                <tbody>
                                <tr class="complete">
                                    <td>
                                        <span class="order-number"> {{__("Order")}} #{{ $order->id }}</span>
                                        <span class="price">{{ amount_with_currency_symbol($order->total_amount) }}</span>
                                    </td>
                                  
                                    <td>
                                        <span class="">{{ $order->created_at?->format("M d, Y") }}</span>
                                        <span class="">{{ $order->created_at?->format("H:ia") }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-wrapper">
                                            @php
                                                $refund_status = \Modules\RefundModule\Entities\RefundProduct::where(['status' => 1, 'order_id' => $order->id, 'user_id' => $order->user_id])->exists();
                                            @endphp
                                            <span class="order-btn-custom status">{{__('Order Status').': '.__($order->status)}}</span>
                                            <span class="order-btn-custom status">{{__('Payment Status').': '.($refund_status ? __('Refunded') : __($order->payment_status))}}</span>

                                            @if($order->transaction_id)
                                                <span class="order-btn-custom status">{{__('Transaction ID').': '.$order->transaction_id}}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Order status end-->

                        <!-- Order summery start -->
                        <div class="order-inner-content-wrap">
                            
                            <div class='row'>
                                <div class='col-6'>
                                    
                                    <div class="billing-info">
                                        <div class="address">
                                            <h5 class="topic-title">{{__("billing information")}}</h5>
                                            <div class="d-flex gap-4">
                                                <p>
                                                    <span class="font-weight-bold">{{__('Name:')}}</span>
                                                    <span>{{$order->name}}</span>
                                                </p>
                                                <p>
                                                    <span class="font-weight-bold">{{__('Email:')}}</span>
                                                    <span>{{$order->email}}</span>
                                                </p>
                                                <p>
                                                    <span class="font-weight-bold">{{__('Phone:')}}</span>
                                                    <span>{{$order->phone}}</span>
                                                </p>
                                            </div>
                                            <div class="d-flex gap-4">
                                                <p>
                                                    <span class="font-weight-bold">{{__('Country:')}}</span>
                                                    <span>{{$order->getCountry?->name}}</span>
                                                </p>
                                                <p>
                                                    <span class="font-weight-bold">{{__('State:')}}</span>
                                                    <span>{{$order->getState?->name}}</span>
                                                </p>
                                                <p>
                                                    <span class="font-weight-bold">{{__('City:')}}</span>
                                                    <span>{{$order->getCity?->name ?? ''}}</span>
                                                </p>
                                            </div>
                                            <div class="d-flex">
                                                <p>
                                                    <span class="font-weight-bold">{{__('Address:')}}</span>
                                                    <span>{{ $order->address }}</span>
                                                </p>
                                            </div>
                                        </div>
        
                                            @if($order->message)
                                                <div class="other_note">
                                                    <div class="d-flex gap-4">
                                                        <p>
                                                            <span class="font-weight-bold">{{__('Other Note:')}}</span>
                                                            <span>{{$order->message}}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        
                                          
                                    </div>
                                </div>
                                <div class='col-6'>
                                    

                                        @if(@$order->shipping_data && @$order->shipping_data->trackingUrl )
                                          
                                                 @php 
                                                   $shippingInfo =  @$order->shipping_data;
                                                 @endphp
                                            
                                                 <div class="billing-info">
                                                    <div class="address">
                                                        <h5 class="topic-title">{{__("DHL Shipping inforamtion")}}</h5>
                                                        <div class="d-flex gap-4">
                                                            <p>
                                                                <span class="font-weight-bold">{{__('Shipment Tracking Number:')}}</span>
                                                                <span>{{$shippingInfo->shipmentTrackingNumber}}</span>
                                                            </p>
                                                            <p>
                                                                <span class="font-weight-bold">{{__('Tracking URL:')}}</span>
                                                                <span> <a  target="_blank" href="{{@$order->shipping_data->trackingUrl }}">
                                                                        {{__('Track Order')}}
                                                                    </a></span>
                                                            </p>
                                                           
                                                        </div>
                                                             @php
                                                               $shippingCharge = @$shippingInfo->shipmentCharges[0];
                                                            @endphp
                                                            
                                                            @if($shippingCharge)
                                                                <div class="d-flex gap-4">
                                                                    
            
                                                    
                                                               
                                                                    <p>
                                                                        <span class="font-weight-bold">{{__('Shipment Charges:')}}</span>
                                                                        <span>{{@$shippingCharge->price}} {{@$shippingCharge->priceCurrency }}</span>
                                                                    </p>
                                                                    
                                                                     <p>
                                                                        <span class="font-weight-bold">{{__('Dispatch Confirmation Number:')}}</span>
                                                                        <span>{{$shippingInfo->dispatchConfirmationNumber}}</span>
                                                                    </p>
                                                                    
                                                                </div>
                                                            @endif
                                                        
                                                        <div class="d-flex gap-4">
      
                                                            @php
                                                               $packages = @$shippingInfo->packages[0];
                                                            @endphp
                                                            <p>
                                                                <span class="font-weight-bold">{{__('Package Reference Number:')}}</span>
                                                                <span>{{@$packages->referenceNumber}} </span>
                                                            </p>
                                                            
                                                              <p>
                                                                <span class="font-weight-bold">{{__('Package tracking number:')}}</span>
                                                                <span>{{@$packages->trackingNumber}} </span>
                                                             </p>
                                                             
                                                             
                                                            
                                                        </div>
                                                        
                                                        
                                                         <div class="d-flex gap-4">
      
                                                   
                                                          
                                                              <p>
                                                                <span class="font-weight-bold">{{__('Package Volumetric Weight:')}}</span>
                                                                <span> 
                                                                 {{@$packages->volumetricWeight }}
                                                                </span>
                                                               </p>
                                                             
                                                               <p>
                                                                <span class="font-weight-bold">{{__('Package Tracking URL:')}}</span>
                                                                <span> <a  target="_blank" href="{{@$packages->trackingUrl }}">
                                                                        {{__('Track')}}
                                                                    </a></span>
                                                               </p>
                                                            
                                                        </div>
                                                        
                                                        
                                                  </div>
        
                                                
                
                                              
                                       
                                               </div>
                                    
                                        @endif
                                          
                                          
                                          
                                            
                                       
                                    
                                </div>
                            </div>
               

                            <ul class="order-summery-list">
                                <li class="single-order-summery border-bottom">
                                    <div class="content border-bottom ex">
                                    <span class="subject text-deep">
                                        {{__("product")}}
                                    </span>
                                        <span class="object text-deep">
                                        {{__("subtotal")}}
                                    </span>
                                    </div>

                                    <ul class="internal-order-summery-list">
                                        @foreach(json_decode($order->order_details) ?? [] as $product)
                                            <li class="internal-single-order-summery">
                                            <span class="internal-subject">{!! render_image_markup_by_attachment_id($product->options?->image) !!} {{ $product?->name }}
                                                @if(!empty($product->options?->color_name))
                                                    : {{ __("Size") }} : {{ $product->options?->color_name }} ,
                                                @endif

                                                @if(!empty($product->options?->size_name))
                                                    {{ __("Color") }} : {{ $product->options?->size_name }}
                                                @endif

                                                @if(!empty($product->options->attributes))
                                                    ,
                                                    @foreach($product->options?->attributes ?? [] as $key => $value)
                                                        {{ $key }} : {{ $value }} @if($loop->last) , @endif
                                                    @endforeach
                                                @endif

                                                <i class="las la-times icon"></i>
                                                <span class="times text-deep">{{ $product->qty }}</span>
                                            </span>
                                                <span class="internal-object">
                                                {{ amount_with_currency_symbol(($product->price * $product->qty) ?? 0) }}
                                            </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                                <li class="single-order-summery border-bottom">
                                    <div class="content">
                                    <span class="subject text-deep">
                                        {{__("subtotal")}}
                                    </span>
                                        <span class="object text-deep">
                                        {{ amount_with_currency_symbol($order_meta->subtotal ?? 0) }}
                                    </span>
                                    </div>
                                </li>
                                <li class="single-order-summery">
                                    @php
                                        $coupon = [];
                                        $coupon_amount = '';
                                        if ($order->coupon)
                                        {
                                            $coupon = \Modules\CouponManage\Entities\ProductCoupon::where('code', $order->coupon)->first();
                                            $coupon_amount = $coupon->discount_type == 'percentage' ? $coupon->discount.'%' : amount_with_currency_symbol($coupon->discount);
                                        }
                                    @endphp
                                    <div class="content">
                                    <span class="subject text-deep">
                                        {{__("coupon discount")}}
                                    </span>
                                        <span class="object">
                                        {{ $coupon ? '-'.$coupon_amount : '' }}
                                    </span>
                                    </div>
                                </li>
                                <li class="single-order-summery">
                                    <div class="content">
                                    <span class="subject text-deep">
                                        {{__("tax")}}
                                    </span>
                                        <span class="object">
                                        +{{ amount_with_currency_symbol($order_meta->product_tax) }}
                                    </span>
                                    </div>
                                </li>
                                <li class="single-order-summery border-bottom">
                                    <div class="content">
                                    <span class="subject text-deep">
                                        {{__("shipping cost")}}
                                    </span>
                                        <span class="object">
                                        +{{ amount_with_currency_symbol($order_meta->shipping_cost) }}
                                    </span>
                                    </div>
                                </li>
                                <li class="single-order-summery border-bottom">
                                    <div class="content total">
                                    <span class="subject text-deep color-main">
                                        {{__("total")}}
                                    </span>
                                        <span class="object text-deep color-main">
                                        {{ amount_with_currency_symbol($order_meta->total) }}
                                    </span>
                                    </div>
                                </li>
                                <li class="single-order-summery">
                                    <div class="content total">
                                    <span class="subject text-deep">
                                        {{__("payment method")}}
                                    </span>
                                        <span class="object">
                                        {{ __($order->payment_gateway) ?? __("Cash on delivery") }}
                                    </span>
                                    </div>
                                </li>
                            </ul>
                            <!-- Order summery end     -->
                        </div>
                        <!-- Order summery end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-media-upload.markup/>
@endsection

@section('scripts')
    <x-media-upload.js/>
@endsection
