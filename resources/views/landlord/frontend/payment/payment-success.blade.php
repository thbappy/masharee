@extends('landlord.frontend.frontend-page-master')
@section('title')
    {{__('Payment Success For:')}} {{$payment_details->package_name}}
@endsection
@section('page-title')
    {{$payment_details->package_name}}
@endsection

@section('style')
    <style>
        .store-icon {
            font-size: 20px;
        }
    </style>
@endsection

@section('content')
    @php
        $site_domain = DB::table('domains')->where('tenant_id', $payment_details->tenant_id)->first();
    @endphp

    <div class="error-page-content" data-padding-bottom="100">
        <div class="container">
            @if(empty($domain))
                <div class="alert alert-danger text-bold text-center mt-2">
                    <i class="las la-info-circle"></i>
                    {{__('Your website is not ready yet, you will get notified by email when it is ready.')}}
                </div>
            @endif

            @if($domain)
                <div class="alert alert-success text-bold text-center mt-2">
                    <h2>{{__('Your website is ready.')}}</h2>
                    <i class="las la-info-circle"></i>
                    {{__('An email has been sent to your email with credentials and instructions for you shop.')}}
                </div>
            @endif


            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="order-success-area margin-bottom-80 text-center pt-5">
                        <h1 class="title">{{get_static_option('site_order_success_page_title')}}</h1>
                        <p class="order-page-description">{{get_static_option('site_order_success_page_description')}}</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="single-billing-items">
                        <h2 class="billing-title">{{__('Order Details')}}</h2>
                        <ul class="billing-details mt-4">
                            <li><strong>{{__('Order ID:')}}</strong> #{{$payment_details->id}}</li>
                            <li class="text-capitalize">
                                <strong>{{__('Payment Package:')}}</strong> {{$payment_details->package_name}}</li>
                            <li class="text-capitalize">
                                <strong>{{__('Payment Package Type:')}}</strong> {{ \App\Enums\PricePlanTypEnums::getText(optional($payment_details->package)->type) }}
                            </li>

                            @if($payment_details->status !== 'trial')
                                <li class="text-capitalize"><strong>{{__('Payment Gateway:')}}</strong>
                                    @php
                                        $gateway = str_replace('_', ' ',$payment_details->package_gateway);
                                    @endphp
                                    {{$gateway}}
                                </li>
                                <li class="text-capitalize">
                                    <strong>{{__('Payment Status:')}}</strong> {{$payment_details->payment_status}}</li>
                                <li><strong>{{__('Transaction ID:')}}</strong> {{$payment_details->transaction_id}}</li>
                            @endif

                            @if(!empty($site_domain))
                                <li><strong>{{__('Shop URL:')}}</strong> <a
                                        href="{{tenant_url_with_protocol($site_domain->domain)}}"
                                        target="_blank">{{$site_domain->domain}}</a></li>
                            @endif
                        </ul>
                    </div>
                    <div class="single-billing-items mt-4">
                        <h2 class="billing-title">{{__('Billing Details')}}</h2>
                        <ul class="billing-details mt-4">
                            <li><strong>{{__('Name')}}</strong> {{$payment_details->name}}</li>
                            <li><strong>{{__('Email')}}</strong> {{$payment_details->email}}</li>
                        </ul>
                    </div>
                    <div class="btn-wrapper mt-5">
                        <a href="{{route('landlord.homepage')}}"
                           class="cmn-btn cmn-btn-bg-1 ">{{__('Back To Home')}}</a>

                        @if(!empty($site_domain))
                            <a href="{{tenant_url_with_protocol($site_domain->domain)}}" class="cmn-btn cmn-btn-bg-4"
                               target="_blank">{{__('Open Shop')}} <i class="store-icon las la-store-alt"></i></a>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="single-price-plan-item">
                        <div class="price-header">
                            <h3 class="title">{{ $payment_details->package_name}}</h3>
                            <div class="price-wrap mt-4"><span
                                    class="price">{{amount_with_currency_symbol($payment_details->package_price)}}  {{ $payment_details->status == 'trial' ? ' - Trial' : '' }}</span>{{ $payment_details->type ?? '' }}
                            </div>
                            <h5 class="title text-primary mt-2">{{__('Start Date :')}}{{$payment_details->start_date ?? ''}}</h5>
                            <h5 class="title text-danger mt-2">{{__('Expire Date :')}}{{$payment_details->expire_date?->format('d-m-Y H:m:s') ?? 'Life Time'}}</h5>
                        </div>
                        <div class="price-body mt-4">
                            <ul class="features">
                                @if(!empty(optional($payment_details->package)->page_permission_feature))
                                    <li class="check"> {{ __(sprintf('Page Create %s', optional($payment_details->package)->page_permission_feature > -1 ? optional($payment_details->package)->page_permission_feature : 'Unlimited' )) }}</li>
                                @endif

                                @if(!empty(optional($payment_details->package)->blog_permission_feature))
                                    <li class="check"> {{ __(sprintf('Blog Create %s', optional($payment_details->package)->blog_permission_feature > -1 ? optional($payment_details->package)->blog_permission_feature : 'Unlimited' ))  }}</li>
                                @endif

                                @if(!empty(optional($payment_details->package)->service_permission_feature))
                                    <li class="check"> {{ __(sprintf('Service Create %s', optional($payment_details->package)->service_permission_feature ?? 'Unlimited' )) }}</li>
                                @endif

                                @if(!empty(optional($payment_details->package)->product_permission_feature))
                                    <li class="check"> {{ __(sprintf('Product Create %s', optional($payment_details->package)->product_permission_feature > -1 ? optional($payment_details->package)->product_permission_feature : 'Unlimited' )) }}</li>
                                @endif

                                @if(!empty(optional($payment_details->package)->storage_permission_feature))
                                    <li class="check"> {{ __(sprintf('Storage Amount %s', optional($payment_details->package)->storage_permission_feature > -1 ? optional($payment_details->package)->storage_permission_feature . ' MB' : 'Unlimited' )) }}</li>
                                @endif

                                @foreach(optional($payment_details->package)->plan_features as $key=> $item)
                                    @continue(in_array($item->feature_name, ['products', 'blog', 'pages', 'storage']))

                                    <li class="check"> {{str_replace('_', ' ',ucfirst($item->feature_name)) ?? ''}}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="price-footer pb-0">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
