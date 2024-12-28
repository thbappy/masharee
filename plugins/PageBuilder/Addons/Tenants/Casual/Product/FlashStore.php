<?php

namespace Plugins\PageBuilder\Addons\Tenants\Casual\Product;

use App\Helpers\LanguageHelper;
use App\Helpers\SanitizeInput;
use Modules\Blog\Entities\BlogCategory;
use Modules\Product\Entities\Product;
use Plugins\PageBuilder\Fields\NiceSelect;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\PageBuilderBase;
use Symfony\Component\Console\Input\Input;
use Plugins\PageBuilder\Fields\ColorPicker;

class FlashStore extends PageBuilderBase
{
    public function preview_image()
    {
        return 'Tenant/common/Screenshot_26.png';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();
        $widget_saved_values = $this->get_settings();

        $output .= Text::get([
            'name' => 'title',
            'label' => __('Section Title'),
            'value' => $widget_saved_values['title'] ?? null,
        ]);

        $output .= Text::get([
            'name' => 'see_all_text',
            'label' => __('See All Button Text'),
            'value' => $widget_saved_values['see_all_text'] ?? ''
        ]);
        
         $output .= ColorPicker::get([
            'name' => 'card_color',
            'label' => __('Card Color'),
            'value' => $widget_saved_values['card_color'] ?? null,
            'info' => '<div class="text-primary mt-1">' . __('Keep empty if you want to keep theme default colors') . '</div>'
        ]);

        
         $output .= ColorPicker::get([
            'name' => 'background_color',
            'label' => __('Background Color'),
            'value' => $widget_saved_values['background_color'] ?? null,
            'info' => '<div class="text-primary mt-1">' . __('Keep empty if you want to keep theme default colors') . '</div>'
        ]);

        $output .= Text::get([
            'name' => 'see_all_url',
            'label' => __('See All Button Link'),
            'value' => $widget_saved_values['see_all_url'] ?? ''
        ]);

        $products = Product::published()->get()->mapWithKeys(function ($item){
            return [$item->id => $item->name];
        })->toArray();

        $output .= NiceSelect::get([
            'multiple' => true,
            'name' => 'products',
            'label' => __('Select Products'),
            'options' => $products,
            'value' => $widget_saved_values['products'] ?? null,
            'info' => __('you can select your desired products or leave it empty')
        ]);

        $output .= Number::get([
            'name' => 'item_show',
            'label' => __('Item Show'),
            'value' => $widget_saved_values['item_show'] ?? null,
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
        $title = SanitizeInput::esc_html($this->setting_item('title') ?? __('Featured Products'));
        $products_id = $this->setting_item('products');
        $item_show = SanitizeInput::esc_html($this->setting_item('item_show') ?? '');

        $see_all_text = esc_html($this->setting_item('see_all_text') ?? '');
        $see_all_url = esc_html($this->setting_item('see_all_url') ?? '');

        $padding_top = SanitizeInput::esc_html($this->setting_item('padding_top'));
        $padding_bottom = SanitizeInput::esc_html($this->setting_item('padding_bottom'));

        $products = Product::where('status_id',1);

        if (!empty($products_id))
        {
            $products->whereIn('id', $products_id);
        }

        $products = $products->withSum('taxOptions', 'rate')->take($item_show ?? 6)->get();

        $data = [
            'title' => $title,
            'see_all_text' => $see_all_text,
            'see_all_url' => $see_all_url,
            'padding_top'=> $padding_top,
            'padding_bottom'=> $padding_bottom,
            'products'=> $products,
            'background_color'=> esc_html($this->setting_item('background_color')),
              'card_color'=> esc_html($this->setting_item('card_color')),
        ];

        return self::renderView('tenant.casual.product.flash_store',$data);
    }

    public function addon_title()
    {
        return __('Casual: Flash Store');
    }
}
