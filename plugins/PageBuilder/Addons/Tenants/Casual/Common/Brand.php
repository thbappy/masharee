<?php

namespace Plugins\PageBuilder\Addons\Tenants\Casual\Common;

use App\Helpers\LanguageHelper;
use App\Helpers\SanitizeInput;
use Modules\Campaign\Entities\Campaign;
use Plugins\PageBuilder\Fields\NiceSelect;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\PageBuilderBase;
use Plugins\PageBuilder\Fields\ColorPicker;

class Brand extends PageBuilderBase
{

    public function preview_image()
    {
        return 'Tenant/common/Screenshot_28.png';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();
        $widget_saved_values = $this->get_settings();

        $brand = \Modules\Attributes\Entities\Brand::select('id', 'name')->get()->mapWithKeys(function ($item) {
            return [$item->id => $item->name];
        })->toArray();

        $output .= NiceSelect::get([
            'multiple' => true,
            'name' => 'brand',
            'label' => __('Select Brand'),
            'options' => $brand,
            'value' => $widget_saved_values['brand'] ?? null,
            'info' => __('You can select your desired campaign or leave it empty')
        ]);

        $output .= Number::get([
            'name' => 'item_show',
            'label' => __('Item Show'),
            'value' => $widget_saved_values['item_show'] ?? null,
        ]);
        
         $output .= ColorPicker::get([
            'name' => 'background_color',
            'label' => __('Background Color'),
            'value' => $widget_saved_values['background_color'] ?? null,
            'info' => '<div class="text-primary mt-1">' . __('Keep empty if you want to keep theme default colors') . '</div>'
        ]);
        
          
        $output .= ColorPicker::get([
            'name' => 'card_color',
            'label' => __('Card Color'),
            'value' => $widget_saved_values['card_color'] ?? null,
            'info' => '<div class="text-primary mt-1">' . __('Keep empty if you want to keep theme default colors') . '</div>'
        ]);


        $output .= Number::get([
            'name' => 'item_pagination',
            'label' => __('Slider Pagination'),
            'value' => $widget_saved_values['item_pagination'] ?? null,
            'info' => __('Select pagination number or leave it empty')
        ]);

        // add padding option
        $output .= $this->padding_fields($widget_saved_values);
        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    public function frontend_render()
    {
        $brands_id = $this->setting_item('brand');
        $item_show = esc_html($this->setting_item('item_show') ?? '');
        $item_pagination = esc_html($this->setting_item('item_pagination') ?? '');
        $padding_top = esc_html($this->setting_item('padding_top'));
        $padding_bottom = esc_html($this->setting_item('padding_bottom'));

        $brands = \Modules\Attributes\Entities\Brand::query();
        if (!empty($brands_id)) {
            $brands->whereIn('id', $brands_id);
        }

        $brands = $brands->take($item_show ?? 8)->get();

        $data = [
            'padding_top' => $padding_top,
            'padding_bottom' => $padding_bottom,
            'brands' => $brands,
            'item_pagination' => $item_pagination ?? 6,
            'background_color'=> esc_html($this->setting_item('background_color')),
            'card_color'=> esc_html($this->setting_item('card_color')),
        ];

        return self::renderView('tenant.casual.common.brand', $data);
    }

    public function addon_title()
    {
        return __('Casual: Brand');
    }
}
