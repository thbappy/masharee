<div class="modal fade" tabindex="-1" id="dhl_shipping_configuration_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize">{{__("DHL Shipping Configuration")}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('tenant.admin.shipping.plugin.configuration.update')}}" method="POST">
                @csrf

                <input type="hidden" name="shipping_gateway_name" value="dhl_shipping">
                <div class="card-body">
                    <!--otp env settings -->
                    <h5 class="mb-4">{{ __('Configure dhl_shipping Options') }}</h5>


                    <div class="mb-2">
                        <x-fields.switcher label="Auto create order" name="dhl_shipping_auto_create_order_option"
                                           info="{{__('Checking this button will auto create orders when any user place an order and complete his/her payment process. Disabling it will not take any action on automatic order creation on dhl_shipping')}}"
                                           info-class="alert alert-info text-dark"
                                           set-value="on"
                                           checked
                                           value="{{get_static_option('dhl_shipping_auto_create_order_option')}}"/>


                    </div>

                    <button type="submit" id="update" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}}
                        <x-btn.button-loader class="d-none"/>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
