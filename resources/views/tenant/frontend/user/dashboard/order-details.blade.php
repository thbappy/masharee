@extends('tenant.frontend.user.dashboard.user-master')

@section('title')
    {{__('Order Details')}}
@endsection

@php
    $order_meta = json_decode($order->payment_meta);
@endphp


@section('section')
    <link rel="stylesheet" href="{{global_asset('assets/common/css/select2.min.css')}}">
    <style>
        .order-wrapper {
            padding: 30px;
        }
        .order-wrapper-flex {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .order-status-wrap .order-wrapper .btn-wrapper .order-btn-custom {
            display: inline-block;
            font-weight: 600;
            background-color: var(--form-bg-color);
            color: #999999;
            padding: 7px 26px 9px;
            border-radius: 30px;
            text-transform: capitalize;
        }
        .order-status-wrap .order-wrapper .btn-wrapper .order-btn-custom.status {
            background-color: #00B106;
            color: #fff;
        }

        .order-status-wrap .order-status-inner .order-number {
            font-family: var(--heading-color);
            font-size: 20px;
            font-weight: 700;
            color: var(--heading-color);
            display: block;
            text-transform: capitalize;
        }
        .order-status-wrap .order-status-inner .price {
            font-family: var(--heading-color);
            font-size: 30px;
            font-weight: 500;
            color: var(--main-color-one);
            display: block;
            line-height: 44px;
            margin-top: 10px;
        }
        .order-status-wrap .order-status-inner .date,
        .order-status-wrap .order-status-inner .time {
            display: block;
            color: #999999;
        }
        
        .color-dark {
            color:dark !important;
        }
    </style>

        <!-- Order status start-->
        <div class="order-status-wrap order-details-page">
            <div class="complete order-wrapper">
                <div class="order-wrapper-flex">
                    <div class="order-wrap-single">
                        <div class="order-status-inner">
                            <span class="order-number"> {{__("order")}} #{{ $order->id }}</span>
                            <span class="price">{{ amount_with_currency_symbol($order->total_amount) }}</span>
                        </div>
                    </div>
                    <div class="order-wrap-single">
                        <div class="order-status-inner">
                            <span class="date">{{ $order->created_at?->format("M d, Y") }}</span>
                            <span class="time">{{ $order->created_at?->format("h:i A") }}</span>
                        </div>
                    </div>
                </div>
                <div class="btn-wrapper d-flex flex-wrap justify-content-end gap-2 mt-3">
                    @php
                        $status_color = $order->status == 'pending' ? 'bg-warning' : 'bg-success';
                        $payment_status_color = $order->payment_status == 'pending' ? 'bg-warning' : 'bg-success';
                    @endphp
                    <span class="order-btn-custom status {{$status_color}}">{{__('Status:')}} {{ __($order->status) }}</span>
                    <span class="order-btn-custom status {{$payment_status_color}}">{{__('Payment:')}} {{ __($order->payment_status) }}</span>
                    @if($order->transaction_id)
                        <span class="order-btn-custom status">{{__('Transaction ID').': '.$order->transaction_id}}</span>
                    @endif
                </div>
            </div>




{{--            <table class="order-status-inner">--}}
{{--                <tbody>--}}
{{--                <tr class="complete">--}}
{{--                    <td>--}}
{{--                        <span class="order-number"> {{__("order")}} #{{ $order->id }}</span>--}}
{{--                        <span class="price">{{ amount_with_currency_symbol($order->total_amount) }}</span>--}}
{{--                    </td>--}}
{{--                    <td>--}}
{{--                        <span class="date">{{ $order->created_at?->format("M d, Y") }}</span>--}}
{{--                        <span class="time">{{ $order->created_at?->format("h:i A") }}</span>--}}
{{--                    </td>--}}
{{--                    <td>--}}
{{--                        <div class="btn-wrapper">--}}
{{--                            @php--}}
{{--                                $status_color = $order->status == 'pending' ? 'bg-warning' : 'bg-success';--}}
{{--                                $payment_status_color = $order->payment_status == 'pending' ? 'bg-warning' : 'bg-success';--}}
{{--                            @endphp--}}
{{--                            <span class="order-btn-custom status {{$status_color}}">{{__('Status:')}} {{ __($order->status) }}</span>--}}
{{--                            <span class="order-btn-custom status {{$payment_status_color}}">{{__('Payment:')}} {{ __($order->payment_status) }}</span>--}}
{{--                            @if($order->transaction_id)--}}
{{--                                <span class="order-btn-custom status">{{__('Transaction ID').': '.$order->transaction_id}}</span>--}}
{{--                            @endif--}}
{{--                        </div>--}}
{{--                    </td>--}}
{{--                </tr>--}}
{{--                </tbody>--}}
{{--            </table>--}}
        </div>
        <!-- Order status end-->

        <!-- Order summery start -->
        <div class="order-inner-content-wrap">
            <h4 class="title">{{__("order details")}}</h4>
            <div class="billing-info">
                <div class="date-time">
                    <span class="date">{{ $order->created_at?->format("M d, Y") }}</span>
                    <span class="time">{{ $order->created_at?->format("h:i A") }}</span>
                </div>

                <div class="address">
                    @php
                        $refund = \Modules\RefundModule\Entities\RefundProduct::where(['user_id' => Auth::guard('web')->user()->id, 'order_id' => $order->id])->first();
                    @endphp

                    @if(isset($refund) && !empty($refund))
                        <p>{{__('Refund Request is Already Sent')}}</p>
                    @endif

                    <a class="btn btn-danger" href="" data-bs-toggle="modal" data-bs-target="#refundModal">{{__('Request Refund')}}</a>
                    <h5 class="topic-title">{{__("billing information")}}</h5>
                    <p class="address">{{ $order->address }}</p>
                </div>
            </div>
            
            
             @if(@$order->shipping_data && @$order->shipping_data->trackingUrl )
                 @php 
                        $shippingInfo =  @$order->shipping_data;
                 @endphp
                                          <h4 class="title">{{__("Shipping Details")}}</h4>
                                              <div class="d-flex gap-4 billing-info">
                                                            <p>
                                                                <span class="font-weight-bold">{{__('Shipment Tracking Number:')}}</span>
                                                                <span   style="color: darkblue;" class='color-dark' >{{$shippingInfo->shipmentTrackingNumber}}</span>
                                                            </p>
                                                            <p>
                                                                <span class="font-weight-bold">{{__('Tracking URL:')}}</span>
                                                                <span style="color: darkblue;" class='color-dark'> <a  target="_blank" href="{{@$order->shipping_data->trackingUrl }}">
                                                                        {{__('Track Order')}}
                                                                    </a></span>
                                                            </p>
                                                           
                                                        </div>
                                                        
                                                        
                                                            @php
                                                               $shippingCharge = @$shippingInfo->shipmentCharges[0];
                                                            @endphp
                                                            
                                                        @if(@$shippingCharge)
                                                            <div class="d-flex gap-4 billing-info">
                                                                
        
                                            
                                                              
                                                                 <p class='text-bold'>
                                                                    <span class="font-weight-bold">{{__('Dispatch Confirmation Number:')}}</span>
                                                                    <span style="color: darkblue;"   class='color-dark'>{{$shippingInfo->dispatchConfirmationNumber}}</span>
                                                                </p>
                                                                
                                                            </div>
                                                        @endif
                                                        
                                                        <div class="d-flex gap-4 billing-info">
      
                                                            @php
                                                               $packages = @$shippingInfo->packages[0];
                                                            @endphp
                                                            <p>
                                                                <span class="font-weight-bold">{{__('Package Reference Number:')}}</span>
                                                                <span style="color: darkblue;"  class='color-dark'>{{@$packages->referenceNumber}} </span>
                                                            </p>
                                                            
                                                              <p>
                                                                <span class="font-weight-bold">{{__('Package tracking number:')}}</span>
                                                                <span style="color: darkblue;"  class='color-dark'>{{@$packages->trackingNumber}} </span>
                                                             </p>
                                                             
                                                             
                                                            
                                                        </div>
                                                        
                                                        
                                                         <div class="d-flex gap-4 billing-info">
      
                                                               <p>
                                                                <span class="font-weight-bold">{{__('Package Tracking URL:')}}</span>
                                                                <span style="color: darkblue;"  class='color-dark'> <a  target="_blank" href="{{@$packages->trackingUrl }}">
                                                                        {{__('Track')}}
                                                                    </a></span>
                                                               </p>
                                                            
                                                        </div>
      
            @endif

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

                                                @if(!empty($product->options?->attributes))
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
                                        {{ $coupon ? '-'.$coupon_amount : 0 }}
                                    </span>
                    </div>
                </li>
                <li class="single-order-summery">
                    <div class="content">
                                    <span class="subject text-deep">
                                        {{__("tax")}}
                                    </span>
                        <span class="object">
                                        {{ $order_meta->product_tax }}%
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
                                        {{ __(str_replace('_',' ',$order->payment_gateway)) ?? __("cash on delivery") }}
                                    </span>
                    </div>
                </li>
            </ul>
            <!-- Order summery end     -->
        </div>
        <!-- Order summery end -->


        <!-- Modal -->
        <div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{__('Refund Request')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('tenant.user.dashboard.package.order.refund')}}" method="POST">
                    @csrf
                    <div class="modal-body">
                            <div>
                                <h5 class="order-number"> {{__("Order")}} #{{ $order->id }}</h5>

                                <div class="d-flex gap-2">
                                    <h5 class="price">{{ amount_with_currency_symbol($order->total_amount) }}</h5>
                                    <div class="btn-wrapper">
                                        @php
                                            $status_color = $order->status == 'pending' ? 'bg-warning' : 'bg-success';
                                            $payment_status_color = $order->payment_status == 'pending' ? 'bg-warning' : 'bg-success';;
                                        @endphp
                                        <span class="p-1 text-dark {{$status_color}}">{{__('Status:')}} {{ __($order->status) }}</span>
                                        <span class="p-1 text-dark {{$payment_status_color}}">{{__('Payment:')}} {{ __($order->payment_status) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <span class="date">{{ $order->created_at?->format("M d, Y") }}</span>
                                <span class="time">{{ $order->created_at?->format("h:i A") }}</span>
                            </div>

                            <input type="hidden" name="order_id" value="{{$order->id}}">
                            @if(count(json_decode($order->order_details, true)) > 1)
                                <div>
                                    <label for="refund_select">{{__('Please select ordered products')}}</label>
                                    <select class="form--control select2" name="refund_products[]" id="refund_select" multiple="multiple">
                                        @foreach(json_decode($order->order_details) as $product)
                                            <option value="{{$product->id}}">{{$product->name}} x {{$product->qty}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" name="refund_products[]" value="{{current(json_decode($order->order_details))->id}}">
                            @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{__('Discard')}}</button>
                        <button type="submit" class="btn btn-primary">{{__('Submit Request')}}</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
@endsection

@section('scripts')
    <script src="{{global_asset('assets/common/js/select2.min.js')}}"></script>
    <script>
        $(function (){
            $(document).ready(function() {
                $('.select2').select2();
            });
        });
    </script>
@endsection
