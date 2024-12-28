<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // For landlord user subscription
        '/paytm-ipn',
        '/cashfree-ipn',
        '/payfast-ipn',
        '/cinetpay-ipn',
        '/zitopay-ipn',
        '/paytabs-ipn',
        '/iyzipay-ipn',

        // For domain reseller
        '/ipn/paytm',
        '/ipn/cashfree',
        '/ipn/payfast',
        '/ipn/cinetpay',
        '/ipn/zitopay',
        '/ipn/paytabs',
        '/ipn/iyzipay',

        // For tenant customer checkout
        '/shop/paytm-ipn',
        '/shop/cashfree-ipn',
        '/shop/payfast-ipn',
        '/shop/cinetpay-ipn',
        '/shop/zitopay-ipn',
        '/shop/iyzipay-ipn',

        // For landlord user dashboard subscription
        '/wallet/paytm-ipn',
        '/wallet/cashfree-ipn',
        '/wallet/payfast-ipn',
        '/wallet/cinetpay-ipn',
        '/wallet/zitopay-ipn',
        '/wallet/toyyibpay-ipn',
    ];
}
