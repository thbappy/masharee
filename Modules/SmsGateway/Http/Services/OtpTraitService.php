<?php

namespace Modules\SmsGateway\Http\Services;

use Modules\SmsGateway\Http\Traits\OtpGlobalTrait;

class OtpTraitService
{
    use OtpGlobalTrait;

    public static function gateways(): array
    {
        return [
            'twilio' => __('Twilio'),
            'msg91' => __('MSG91')
        ];
    }

    public function send($data, $type='notify', $sms_type='register', $user='user')
    {
        return $this->sendSms($data, $type, $sms_type, $user);
    }
}
