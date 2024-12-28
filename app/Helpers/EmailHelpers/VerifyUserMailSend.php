<?php

namespace App\Helpers\EmailHelpers;

use App\Mail\BasicMail;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Part\TextPart;
use Symfony\Component\Mime\Part\HtmlPart;
class VerifyUserMailSend
{
    public static function sendMail(User $user){
        
         $token = Str::random(8);

        User::find($user->id)->update(['email_verify_token' => $token]);
        
        $msg = MarkupGenerator::paragraph(__('Hello'));
        $msg .= MarkupGenerator::paragraph(__('Here is your verification code'));
        $msg .= MarkupGenerator::code($token);
        
        $subject = sprintf(__('Verify your email address at %s'), site_title());
        
        $emailFrom = is_null(tenant()) ? get_static_option_central('site_global_email') : get_static_option('tenant_site_global_email');


      
        // $token = Str::random(8);
        // User::find($user->id)->update(['email_verify_token' => $token ]);
        // $msg = MarkupGenerator::paragraph(__('Hello'));
        // $msg .= MarkupGenerator::paragraph(__('Here is your verification code'));
        // $msg .= MarkupGenerator::code($token);
        // $subject = sprintf(__('Verify your email address at %s'),site_title());
        
        //   $emailFrom = is_null(tenant()) ? get_static_option_central('site_global_email') : get_static_option('tenant_site_global_email');


        //   Mail::send([], [], function ($message) use ($user, $msg, $subject ,$emailFrom ) {

        //             $message->to($user->email) 
        //             ->subject($subject)
        //             ->from($emailFrom,$emailFrom)
        //             ->setBody($msg, 'text/html','utf-8');
                
        //     });


        try {
            // Use the html method to send the raw HTML content
                Mail::html($msg, function ($message) use ($user, $subject, $emailFrom) {
                    $message->to($user->email)
                            ->subject($subject)
                            ->from($emailFrom, $emailFrom);
                });
        }catch (\Exception $e){
       
            if ($e->getCode() == 553)
            {
                return redirect()->back()->with(['msg'=> __('Site or server email configuration  is incorrect'), 'type'=> 'danger']);
            }
//            return redirect()->back()->with(['msg'=> $e->getMessage(), 'type'=> 'danger']);
        }
    }

    public static function sendMail_tenant_admin(Admin $user){

        $token = Str::random(8);
        $user_info = tenant()->user()->first();
        $user_info->email_verify_token = $token;
        $user_info->save();
       // Admin::find($user->id)->update(['email_verify_token' => $token ]);
        $msg = MarkupGenerator::paragraph(__('Hello'));
        $msg .= MarkupGenerator::paragraph(__('Here is your verification code'));
        $msg .= MarkupGenerator::code($token);
        $subject = sprintf(__('Verify your email address at %s'),site_title());

        try {
            Mail::to($user->email)->send(new BasicMail($msg,$subject));
        }catch (\Exception $e){
            return redirect()->back()->with(['msg'=> $e->getMessage(), 'type'=> 'danger']);
        }
    }
}
