@extends('tenant.admin.admin-master')
@section('title') {{__('Email Settings')}} @endsection

@section('content')
    <div class="col-12 stretch-card">
        <div class="row g-4">
            <div class="col-lg-12">
                <x-error-msg/>
                <x-flash-msg/>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-5">{{__('SMTP Settings')}}</h4>

                        <form class="forms-sample" method="post" action="{{route('tenant.admin.general.email.settings')}}">
                            @csrf
                            <x-fields.input type="email" value="{{get_static_option('tenant_site_global_email')}}" name="site_global_email" label="{{__('Site Global Email')}}" info="{{__('you will get all mail to this email, also this will be in your user form address in all the mail send from the system.')}}"/>
                            <x-fields.input value="{{get_static_option('site_smtp_host')}}" name="site_smtp_host" label="{{__('SMTP host')}}" />
                            <x-fields.input value="{{get_static_option('site_smtp_username')}}" name="site_smtp_username" label="{{__('SMTP Username')}}" />
                            <x-fields.input type="password" value="{{get_static_option('site_smtp_password')}}" name="site_smtp_password" label="{{__('SMTP Password')}}" />
                            <x-fields.select name="site_smtp_driver"  title="{{__('SMTP Driver')}}">
                                <option {{get_static_option('site_smtp_driver') == 'smtp' ? 'selected' : ''}} value="smtp">{{__('smtp')}}</option>
                                <option {{get_static_option('site_smtp_driver') == 'sendmail' ? 'selected' : ''}} value="sendmail">{{__('sendmail')}}</option>
                                <option {{get_static_option('site_smtp_driver') == 'mailgun' ? 'selected' : ''}} value="mailgun">{{__('mailgun')}}</option>
                                <option {{get_static_option('site_smtp_driver') == 'postmark' ? 'selected' : ''}} value="postmark">{{__('postmark')}}</option>
                            </x-fields.select >
                            <x-fields.select name="site_smtp_port"  title="{{__('SMTP Port')}}">
                                <option {{get_static_option('site_smtp_port') == '25' ? 'selected' : ''}} value="25">25</option>
                                <option {{get_static_option('site_smtp_port') == '587' ? 'selected' : ''}} value="587">587</option>
                                <option {{get_static_option('site_smtp_port') == '465' ? 'selected' : ''}} value="465">465</option>
                                <option {{get_static_option('site_smtp_port')== '2525' ? 'selected' : ''}} value="2525">2525</option>
                            </x-fields.select >
                            <x-fields.select name="site_smtp_encryption" title="{{__('SMTP Encryption')}}">
                                <option {{get_static_option('site_smtp_encryption') == 'ssl' ? 'selected' : ''}} value="ssl">{{__('SSL')}}</option>
                                <option {{get_static_option('site_smtp_encryption') == 'tls' ? 'selected' : ''}} value="tls">{{__('TLS')}}</option>
                                <option {{get_static_option('site_smtp_encryption') == '' ? 'selected' : ''}} value="null">{{__('none')}}</option>
                            </x-fields.select >
                            <button type="submit" class="btn btn-gradient-primary me-2">{{__('Save Changes')}}</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">{{__('Send Test Mail')}}</h4>
                        <p class="text-primary mb-4">
                            {{__('If you see any error here, please contact your hosting provider to make sure you have added valid and proper smtp details.')}}
                        </p>
                        <form class="forms-sample" method="post" action="{{route('tenant.admin.general.mail.settings')}}">
                            @csrf
                            <x-fields.input  name="email" label="{{__('Email')}}" />
                            <button type="submit" class="btn btn-gradient-primary me-2">{{__('Send Test Mail')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

