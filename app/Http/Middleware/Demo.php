<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use App\Models\Page;

class Demo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
     
        $not_allow_path = [
            'register',
            'admin-home',
            'user-home',
            'custom-domain',
            'user/profile-update',
            'user/password-change',
            'order-confirm',
            'user/trial/account'
        ];
        $allow_path = [
            'admin-home/visited/os',
            'admin-home/visited/browser',
            'admin-home/visited/device',
            'admin-home/visited-url',
            'admin-home/topbar/chart',
            'admin-home/topbar/chart/day',

            'shop/cart/ajax/add-to-cart',
            'shop/cart/ajax/update-quantity',
            'shop/cart/ajax/remove',
            'admin-home/pos/hold-order',
            'admin-home/domain-reseller/search-domain'
        ];
        $contains = Str::contains($request->path(), $not_allow_path);

        $msg = 'This is demonstration purpose only, you may not able to change few settings, you will get working file when you purchased it.';

//        if (!empty(tenant()) && tenant()->id == 'medicom')
//        {
//            return $next($request);
//        }

        if($request->isMethod('POST') || $request->isMethod('PUT')) {

            if($request->path() === 'register'){
                return response()->json(
                    [
                        'errors' => [
                            'demo_error' => ['This is demonstration purpose only, you are not allowed to register account, use demo user login details instead'],
                            'username' => ['username: test'],
                            'password' => ['Password: 12345678']
                        ]
                    ],'422');
            }

            if (str_contains(url()->previous(), 'pos') && !in_array($request->path(),$allow_path))
            {
                return response()->json([
                    'type' => 'error',
                    'msg' => $msg,
                    'timer' => 3000
                ]);
            }

            if($contains && !in_array($request->path(),$allow_path)){
                if ($request->ajax()){
                    return response()->json(['type' => 'warning' , 'msg' => $msg]);
                }
                return redirect()->back()->with(['type' => 'warning' , 'msg' => $msg]);
            }
        }

        return $next($request);
    }
}
