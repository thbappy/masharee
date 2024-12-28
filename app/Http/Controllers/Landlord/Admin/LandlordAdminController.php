<?php

namespace App\Http\Controllers\Landlord\Admin;

use App\Helpers\ModuleMetaData;
use App\Http\Controllers\Controller;
use App\Mail\BasicMail;
use App\Models\Admin;
use App\Models\Brand;
use App\Models\PaymentLogs;
use App\Models\PricePlan;
use App\Models\Tenant;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\Blog\Entities\Blog;
use function view;
use Illuminate\Support\Facades\DB;

class LandlordAdminController extends Controller
{
    private const BASE_VIEW_PATH = 'landlord.admin.';

    public function dashboard(){
        $total_admin= Admin::count();

        $total_user = 0;
        try{
             $total_user= User::count();
        }catch(\Exception $e){

        }

        $all_tenants = Tenant::whereValid()->count();
        $total_price_plan = PricePlan::count();
        $total_brand = Brand::all()->count();
        $total_testimonial = Testimonial::all()->count();
        $recent_order_logs = PaymentLogs::orderBy('id','desc')->take(5)->get();


        // All Shop Database and Table Details
        // Product table all column show code  start


        // $tenants = DB::table('tenants')->get(); // Fetch all tenants
        
        // $shopDatabaseDetails = [];
        
        // foreach ($tenants as $tenant) {
        //     $tenantData = json_decode($tenant->data, true);
        
        //     if (!isset($tenantData['tenancy_db_name'])) {
        //         echo "No tenancy_db_name found for tenant ID: {$tenant->id}" . "<br/>";
        //         continue;
        //     }
        
        //     $databaseName = $tenantData['tenancy_db_name'];
        
        //     // Validate database name length
        //     if (strlen($databaseName) > 64) {
        //         echo "Skipping tenant ID: {$tenant->id} - Database name '{$databaseName}' exceeds maximum length" . "<br/>";
        //         continue;
        //     }
        
        //     // Dynamically set the tenant's database connection
        //     config(['database.connections.tenant' => [
        //         'driver' => 'mysql',
        //         'host' => env('DB_HOST', '127.0.0.1'),
        //         'port' => env('DB_PORT', '3306'),
        //         'database' => $databaseName,
        //         'username' => env('DB_USERNAME', 'root'),
        //         'password' => env('DB_PASSWORD', ''),
        //         'charset' => 'utf8mb4',
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'prefix' => '',
        //         'strict' => true,
        //     ]]);
        
        //     DB::purge('tenant');
        //     DB::reconnect('tenant');
        
        //     try {
        //         // Check if the products table exists
        //         $tableExists = DB::connection('tenant')
        //             ->select("SHOW TABLES LIKE 'products'");
        
        //         if ($tableExists) {
        //             // Retrieve column names of the products table
        //             $columns = DB::connection('tenant')
        //                 ->select("SHOW COLUMNS FROM products");
        
        //             $columnNames = array_map(function ($column) {
        //                 return $column->Field; // Get the column name
        //             }, $columns);
        
        //             $tenantDetails = [
        //                 'tenant_id' => $tenant->id,
        //                 'database_name' => $databaseName,
        //                 'columns' => $columnNames,
        //             ];
        
        //             $shopDatabaseDetails[] = $tenantDetails;
        
        //             echo "Tenant ID: {$tenant->id}, Database: {$databaseName}, Products Table Columns:\n" . "<br/>";
        //             foreach ($columnNames as $columnName) {
        //                 echo "- {$columnName}" . "<br/>";
        //             }
        //         } else {
        //             echo "Tenant ID: {$tenant->id} - Products table does not exist in the database '{$databaseName}'" . "<br/>";
        //         }
        //     } catch (\Exception $e) {
        //         echo "Error accessing products table for tenant ID: {$tenant->id} - {$e->getMessage()}" . "<br/>";
        //         continue;
        //     }
        // }
        
        // // Optionally save details to a file
        // file_put_contents('shop_products_columns.json', json_encode($shopDatabaseDetails, JSON_PRETTY_PRINT));
        
        // dd('done');

     // Product table all column show code end
 
 
 
 
 
 
 
 
 
  

        return view(self::BASE_VIEW_PATH.'admin-home',compact('total_admin','total_user','all_tenants','total_brand','total_price_plan','total_testimonial','recent_order_logs'));
    }

    public  function health()
    {
        $all_user = Admin::all()->except(Auth::id());
        return view(self::BASE_VIEW_PATH.'health')->with(['all_user' => $all_user]);
    }

    public function change_password(){
        return view(self::BASE_VIEW_PATH.'auth.change-password');
    }
    public function edit_profile(){
        return view(self::BASE_VIEW_PATH.'auth.edit-profile');
    }
    public function update_change_password(Request $request){
        $this->validate($request,[
            'password' => 'required|confirmed|min:8'
        ]);

        Admin::find(auth('admin')->id())->update(['password'=> Hash::make($request->password)]);
        //store this data in landlord database
        Auth::guard('admin')->logout();
        return response()->success(__('Password Change Success'));
    }
    public function update_edit_profile(Request $request){
        $this->validate($request,[
            'name' => 'required|string',
            'email' => 'required|email|unique:admins,email,'.auth('admin')->id(),
            'mobile' => 'nullable|numeric',
            'image' => 'nullable|integer',
        ]);

        Admin::find(auth('admin')->id())->update([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile ,
            'image' => $request->image ,
        ]);

        //store this data in landlord database
        return response()->success(__('Settings Saved'));
    }

    public function topbar_settings()
    {
        return view('landlord.admin.topbar-settings');
    }

    public function update_topbar_settings(Request $request)
    {
        $request->validate([
            'topbar_twitter_url'=>'nullable',
            'topbar_linkedin_url'=>'nullable',
            'topbar_facebook_url'=>'nullable',
            'topbar_youtube_url'=>'nullable',
            'landlord_frontend_language_show_hide'=>'nullable',
        ]);

        $data = [
            'topbar_twitter_url',
            'topbar_linkedin_url',
            'topbar_facebook_url',
            'topbar_youtube_url',
            'landlord_frontend_language_show_hide',
        ];

        foreach ($data as $item)
        {
            update_static_option($item, $request->$item);
        }

        return response()->success(__('Settings Saved'));
    }

    public function get_chart_data_month(Request $request){
        /* -------------------------------------
            TOTAL ORDER BY MONTH CHART DATA
        ------------------------------------- */
        $all_donation_by_month = PaymentLogs::select('package_price','created_at')->where(['payment_status' => 'complete'])
            ->whereYear('created_at',date('Y'))
            ->get()
            ->groupBy(function ($query){
                return Carbon::parse($query->created_at)->format('F');
            })->toArray();
        $chart_labels = [];
        $chart_data= [];
        foreach ($all_donation_by_month as $month => $amount){
            $chart_labels[] = $month;
            $chart_data[] =  array_sum(array_column($amount,'package_price'));
        }
        return response()->json( [
            'labels' => $chart_labels,
            'data' => $chart_data
        ]);
    }

    public function get_chart_by_date_data(Request $request){
        /* -----------------------------------------------------
           TOTAL ORDER BY Per Day In Current month CHART DATA
       -------------------------------------------------------- */
        $all_donation_by_month = PaymentLogs::select('package_price','created_at')->where(['payment_status' => 'complete'])
            // ->whereMonth('created_at',date('m'))
            ->whereDate('created_at', '>', Carbon::now()->subDays(30))
            ->get()
            ->groupBy(function ($query){
                return Carbon::parse($query->created_at)->format('D, d F Y');
            })->toArray();
        $chart_labels = [];
        $chart_data= [];
        foreach ($all_donation_by_month as $month => $amount){
            $chart_labels[] = $month;
            $chart_data[] =  array_sum(array_column($amount,'package_price'));
        }

        return response()->json( [
            'labels' => $chart_labels,
            'data' => $chart_data
        ]);
    }
}
