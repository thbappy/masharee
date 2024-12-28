<!--<div class="modal fade" tabindex="-1" id="aramex_shipping_modal">-->
<!--    <div class="modal-dialog">-->
<!--        <div class="modal-content">-->
<!--            <div class="modal-header">-->
<!--                <h5 class="modal-title text-capitalize">{{ __('Aramex') }}</h5>-->
<!--                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
<!--            </div>-->

<!--            <form action="{{ route('tenant.admin.shipping.plugin.settings.update') }}" method="POST">-->
<!--                @csrf-->

<!--                <input type="hidden" name="shipping_gateway_name" value="aramex_shipping">-->
<!--                <div class="card-body">-->
                    <!--otp env settings -->
<!--                    <h5 class="mb-4">{{ __('Configure Aramex Credentials') }}</h5>-->
<!--                    <div class="form-group mt-3">-->
<!--                        <label for=""><strong>{{ __('Username') }} <span class="text-danger">*</span>-->
<!--                            </strong></label>-->
<!--                        <input type="text" class="form-control" name="aramex_shipping_username" value="{{get_static_option('aramex_shipping_username')}}"-->
<!--                            placeholder="{{ __('Username') }}">-->
<!--                    </div>-->

<!--                    <div class="form-group mt-3">-->
<!--                        <label for=""><strong>{{ __('Password') }} <span class="text-danger">*</span>-->
<!--                            </strong></label>-->
<!--                        <input type="text" class="form-control" name="aramex_shipping_password" value=""-->
<!--                            placeholder="{{ __('Password') }}">-->
<!--                    </div>-->

<!--                    <div class="form-group mt-3">-->
<!--                        <label for=""><strong>{{ __('Aramex Account Number') }} <span-->
<!--                                    class="text-danger">*</span>-->
<!--                            </strong></label>-->
<!--                        <input type="text" class="form-control" name="aramex_shipping_account_number" value=""-->
<!--                            placeholder="{{ __('Account Number') }}">-->
<!--                    </div>-->

<!--                    <div class="form-group mt-3">-->
<!--                        <label for=""><strong>{{ __('Aramex Account Pin') }} <span class="text-danger">*</span>-->
<!--                            </strong></label>-->
<!--                        <input type="text" class="form-control" name="aramex_account_pin" value=""-->
<!--                            placeholder="{{ __('Account Pin') }}">-->
<!--                    </div>-->

<!--                    <div class="form-group mt-3">-->
<!--                        <label for=""><strong>{{ __('Aramex Client Code') }} <span class="text-danger">*</span>-->
<!--                            </strong></label>-->
<!--                        <input type="text" class="form-control" name="aramex_client_code" value=""-->
<!--                            placeholder="{{ __('Client Code') }}">-->
<!--                    </div>-->


<!--                    <div class="form-group mt-3">-->
<!--                        <label for=""><strong>{{ __('API URL') }} <span class="text-danger">*</span>-->
<!--                            </strong></label>-->
<!--                        <input type="text" class="form-control" name="aramex_shipping_api_url" value=""-->
<!--                            placeholder="{{ __('API Endpoint') }}">-->
<!--                    </div>-->

<!--                    <button type="submit" id="update"-->
<!--                        class="btn btn-primary mt-4 pr-4 pl-4">{{ __('Update Changes') }} <x-btn.button-loader-->
<!--                            class="d-none" /></button>-->
<!--                </div>-->
<!--            </form>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
