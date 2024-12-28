<?php

namespace App\Providers;

use App\Helpers\LanguageHelper;
use App\Helpers\ModuleMetaData;
use App\Helpers\SidebarMenuHelper;
use App\Helpers\ThemeMetaData;
use App\Http\Services\RenderImageMarkupService;
use App\Models\Themes;
use App\Models\User;
use App\Observers\TenantRegisterObserver;
use App\Observers\WalletBalanceObserver;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Modules\Blog\Entities\BlogCategory;
use Modules\Wallet\Entities\Wallet;
use function Psy\bin;
use App\Models\Page;
use Illuminate\Support\Str;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        app()->singleton('LandlordAdminMenu',function (){
           return  new SidebarMenuHelper();
        });
        app()->singleton('GlobalLanguage',function (){
           return  new LanguageHelper();
        });

        $this->app->singleton('ThemeDataFacade', function (){
            return new ThemeMetaData();
        });
        $this->app->singleton('ModuleDataFacade', function (){
            return new ModuleMetaData();
        });
        $this->app->singleton('ImageRenderFacade', function (){
            return new RenderImageMarkupService();
        });

        /* LARAVEL TELESCOPE */
        if ($this->app->environment('local') && in_array(request()->getHost(), ['nazmart.test','127.0.0.1','localhost'])) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        if (get_static_option('site_force_ssl_redirection') === 'on'){
            URL::forceScheme('http');
        }



        /**
         * Setup micros for mediauploader url or cloudlflareurl
         * */

        Storage::macro("renderUrl", function ($filepath, $size = null, $load_from = 0)
        {
            $prefix = !empty($size) ? '' : $size."/".$size."-";
            if ($size == ""){
                $prefix = "";
            }

            if ($prefix == "full"){
                $prefix = "";
            }

            $driver = Storage::getDefaultDriver();

            if ($load_from === 0 && !is_null(tenant())){
                $driver = "TenantMediaUploader";
            }else{
                $driver = "LandlordMediaUploader";
            }

            $file_url = Storage::disk($driver)->url($prefix.$filepath);

            if ($load_from == 0 && !is_null(tenant())){
                return str_replace("/storage",url("/assets/tenant/uploads/media-uploader/".tenant()->getTenantKey().$prefix),$file_url);
            }elseif($load_from == 0 && is_null(tenant())){
                return str_replace("/storage",url("/assets/landlord/uploads/media-uploader").$prefix,$file_url);
            }


            if (Storage::getDefaultDriver() == "TenantMediaUploader"){
                return str_replace("/storage",url("/assets/tenant/uploads/media-uploader/".tenant()->getTenantKey().$prefix),$file_url);
            }

            if (Storage::getDefaultDriver() == "LandlordMediaUploader"){
                return str_replace("/storage",url("/assets/landlord/uploads/media-uploader").$prefix,$file_url);
            }

            $folder_prefix = "";
            if (!is_null(tenant())){
                $folder_prefix = tenant()->getTenantKey()."/";
            }

            if (cloudStorageExist() && Storage::getDefaultDriver() == "wasabi"){
//                $bucket = get_static_option_central('wasabi_bucket') ?? '';
//                $endpoint = get_static_option_central('wasabi_url') ?? '';

//                $path = str_replace("https://".$bucket.".".str_replace("https://","",$endpoint."/"),"",$file_url);
                $filepath = tenant() ? tenant()->id.'/'.$filepath : $filepath;
                $finalUrl = renderWasabiCloudFile($filepath);

                return $finalUrl;
            }


            if (cloudStorageExist() && Storage::getDefaultDriver() == "s3"){
                $tempUrl = Storage::temporaryUrl($folder_prefix.$prefix.$filepath,Carbon::now()->addMinutes(20));
                return $tempUrl;
            }

//            $tempUrl = Cache::remember($filepath,Carbon::now()->addMinutes(15),function ()use($filepath){
//                Storage::temporaryUrl($filepath,Carbon::now()->addMinutes(20));
//            });

            $tempUrl = Storage::temporaryUrl($folder_prefix.$prefix.$filepath,Carbon::now()->addMinutes(20));

            //cloudflare temporary url
            $finalUrl = str_replace([
                "https://".get_static_option_central('cloudflare_r2_bucket').".".str_replace("https://","",get_static_option_central('cloudflare_r2_endpoint'))
            ],[
                "https://".get_static_option_central('cloudflare_r2_url')
            ],$tempUrl);

            return $finalUrl;
        });
    }
}
