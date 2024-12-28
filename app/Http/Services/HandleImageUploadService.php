<?php

namespace App\Http\Services;

use App\Models\MediaUploader;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use phpDocumentor\Reflection\Types\Self_;

class HandleImageUploadService
{
    public static function handle_image_upload(
        $image_db,
        $image,
        $image_name_with_ext,
        $folder_path,
        $request,
        $woocommerce_import = false,
        $woocommerce_import_path = ''
    )
    {
        $image_dimension = getimagesize($image);
        $image_width = $image_dimension[0];
        $image_height = $image_dimension[1];
        $image_dimension_for_db = $image_width . ' x ' . $image_height . ' pixels';
        $image_size_for_db = $image->getSize();

        $image_grid = 'grid-'.$image_db ;
        $image_large = 'large-'. $image_db;
        $image_thumb = 'thumb-'. $image_db;
        $image_tiny = 'tiny-'. $image_db;

        $resize_grid_image = Image::make($image)->resize(350, null,function ($constraint) {
            $constraint->aspectRatio();
        });
        $resize_large_image = Image::make($image)->resize(740, null,function ($constraint) {
            $constraint->aspectRatio();
        });
        $resize_thumb_image = Image::make($image)->resize(150, 150);
        $resize_tiny_image = Image::make($image)->resize(15, 15)->blur(50);

        // todo::convert this woocommerce import into cloud too
        if ($woocommerce_import && !empty($woocommerce_import_path))
        {
            \File::move($woocommerce_import_path.$image_db, $folder_path.$image_db);
        }

        $storage_driver = Storage::getDefaultDriver();

        $imageData = [
            'title' => $image_name_with_ext,
            'size' => formatBytes($image_size_for_db),
            'path' => $image_db,
            'user_type' => 0, //0 == admin 1 == user
            'user_id' => \Auth::guard('admin')->id(),
            'dimensions' => $image_dimension_for_db,
            'is_synced' => in_array(get_static_option_central('storage_driver'),['s3','cloudFlareR2','wasabi'])? 1 : 0,
            'load_from' => in_array($storage_driver,['TenantMediaUploader','LandlordMediaUploader']) ? 0 : 1
        ];

        if ($request->user_type === 'user'){
            $imageData['user_type'] = 1;
            $imageData['user_id'] = \Auth::guard('web')->id();
        }
        else if ($request->user_type === 'api'){
            $imageData['user_type'] = 1;
            $imageData['user_id'] = \Auth::guard('sanctum')->id();
        }

        $image_data = MediaUploader::create($imageData);
        $upload_folder = '/';
        if (cloudStorageExist() && in_array(Storage::getDefaultDriver(), ['s3','cloudFlareR2','wasabi'])){
            $upload_folder = is_null(tenant()) ? '/' : tenant()->getTenantKey().'/';
        }

        Storage::putFileAs($upload_folder, $image, $image_db, 'public'); //$request->file->move($folder_path, $image_db);

        if ($image_width > 150)
        {
            self::mkdirByPath($folder_path .'thumb/');
            self::mkdirByPath($folder_path .'grid/');
            self::mkdirByPath($folder_path .'large/');
            self::mkdirByPath($folder_path .'tiny/');

            $resize_thumb_image->save($folder_path .'thumb/'. $image_thumb);
            $resize_grid_image->save($folder_path .'grid/'. $image_grid);
            $resize_large_image->save($folder_path .'large/'. $image_large);
            $resize_tiny_image->save($folder_path .'tiny/'. $image_tiny);

            Storage::put($upload_folder.'thumb/' . $image_thumb, $resize_thumb_image->encode(), 'public');
            Storage::put($upload_folder.'grid/' . $image_grid, $resize_grid_image->encode(), 'public');
            Storage::put($upload_folder.'large/' . $image_large, $resize_large_image->encode(), 'public');
            Storage::put($upload_folder.'tiny/' . $image_tiny, $resize_tiny_image->encode(), 'public');
        }

        return $image_data->id ?? '';
    }

    public static function mkdirByPath($path)
    {
        if (!is_dir($path))
        {
            return mkdir($path, 0777);
        }

    }
}
