<?php

namespace Plugins\PageBuilder\Addons\Tenants\Aromatic\Product;

use App\Helpers\SanitizeInput;
use Modules\Attributes\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductCategory;
use Plugins\PageBuilder\Fields\Image;
use Plugins\PageBuilder\Fields\NiceSelect;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Switcher;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\PageBuilderBase;

class NewCollection extends PageBuilderBase
{
    public function preview_image()
    {
        return 'Tenant/common/brand-01.png';
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

        $output .= Switcher::get([
            'name' => 'title_line',
            'label' => __('Show/Hide Title Line'),
            'value' => $widget_saved_values['title_line'] ?? null,
        ]);

        $products = Product::where(['status_id' => 1])->get()->mapWithKeys(function ($item){
            return [$item->id => $item->name];
        })->toArray();

        $output .= NiceSelect::get([
            'multiple' => true,
            'name' => 'products',
            'label' => __('Select Products'),
            'options' => $products,
            'value' => $widget_saved_values['products'] ?? null,
            'info' => __('You can select your desired products or leave it empty to show latest products')
        ]);

        $output .= Number::get([
            'name' => 'item_show',
            'label' => __('Product Show'),
            'value' => $widget_saved_values['item_show'] ?? null,
            'info' => 'How many products will be shown'
        ]);

        $output .= Select::get([
            'name' => 'item_order',
            'label' => __('Product Order'),
            'options' => [
                'desc' => __('Descending'),
                'asc' => __('Ascending')
            ],
            'value' => $widget_saved_values['item_order'] ?? null,
            'info' => 'Product order, descending or ascending'
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
        $product_id = $this->setting_item('products');
        $title = SanitizeInput::esc_html($this->setting_item('title') ?? '');
        $title_line = $this->setting_item('title_line');
        $item_show = SanitizeInput::esc_html($this->setting_item('item_show') ?? '');
        $item_order = SanitizeInput::esc_html($this->setting_item('item_order') ?? '');

        $padding_top = SanitizeInput::esc_html($this->setting_item('padding_top'));
        $padding_bottom = SanitizeInput::esc_html($this->setting_item('padding_bottom'));

        $products = Product::with('badge', 'campaign_product', 'inventory', 'inventoryDetail')
                    ->where('status_id', 1);

        if (!empty($product_id))
        {
            $products->whereIn('id', $product_id);
        }

        $products = $products->orderBy('created_at', $item_order ?? 'desc')->withSum('taxOptions', 'rate')->take($item_show ?? 4)->get();

        $data = [
            'padding_top'=> $padding_top,
            'padding_bottom'=> $padding_bottom,
            'title' => $title,
            'title_line' => $title_line,
            'products'=> $products,
        ];

        return self::renderView('tenant.aromatic.product.new_collection', $data);
    }

    public function addon_title()
    {
        return __('Aromatic: New Collection');
    }
}
