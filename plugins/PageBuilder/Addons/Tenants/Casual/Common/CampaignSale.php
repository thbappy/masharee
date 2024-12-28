<?php

namespace Plugins\PageBuilder\Addons\Tenants\Casual\Common;

use App\Helpers\SanitizeInput;
use Modules\Campaign\Entities\Campaign;
use Plugins\PageBuilder\Fields\Image;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\PageBuilderBase;
use Plugins\PageBuilder\Fields\ColorPicker;

use function __;

class CampaignSale extends PageBuilderBase
{
    public function preview_image()
    {
        return 'Tenant/common/casual_camp.png';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();

        $widget_saved_values = $this->get_settings();

        $output .= Text::get([
            'name' => 'title',
            'label' => __('Title'),
            'value' => $widget_saved_values['title'] ?? '',
        ]);

        $campaigns = Campaign::where('status', 'publish')->pluck('title', 'id')->toArray();
        $output .= Select::get([
            'name' => 'campaign',
            'label' => __('Select Campaign'),
            'options' => $campaigns,
            'value' => $widget_saved_values['campaign'] ?? '',
        ]);

        $output .= Number::get([
            'name' => 'discount',
            'label' => __('Discount Percentage'),
            'value' => $widget_saved_values['discount'] ?? '',
            'info' => 'Discount in percentage'
        ]);

        $output .= Text::get([
            'name' => 'button_text',
            'label' => __('Button Text'),
            'value' => $widget_saved_values['button_text'] ?? '',
        ]);

        $output .= Text::get([
            'name' => 'button_url',
            'label' => __('Button URL'),
            'value' => $widget_saved_values['button_url'] ?? '',
            'info' => 'Leave it empty if you do not use external link for the button'
        ]);

        $output .= Image::get([
            'name' => 'image',
            'label' => __('Image '),
            'value' => $widget_saved_values['image'] ?? null,
            'dimensions' => '~1710x612 px'
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
        
        

        $output .= Image::get([
            'name' => 'background_shape',
            'label' => __('Background Shape '),
            'value' => $widget_saved_values['background_shape'] ?? null,
            'dimensions' => '~1710x612 px'
        ]);

//        $output .= Select::get([
//            'name' => 'align_image',
//            'label' => __('Align Image'),
//            'options' => [
//                'left' => 'Left',
//                'right' => 'Right',
//            ],
//            'value' => $widget_saved_values['align_image'] ?? 'left',
//        ]);

        // add padding option
        $output.= $this->padding_fields($widget_saved_values);
        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }

    public function frontend_render()
    {
        $title = esc_html($this->setting_item('title')) ?? '';
        $button_text = esc_html($this->setting_item('button_text')) ?? '';
        $campaign = $this->setting_item('campaign') ?? '';
        $discount = esc_html($this->setting_item('discount')) ?? '';
        $button_url = esc_url($this->setting_item('button_url')) ?? '';
        $image = $this->setting_item('image') ?? '';
        $background_shape = $this->setting_item('background_shape') ?? '';
//        $align_image = $this->setting_item('align_image');
        $padding_top = esc_html($this->setting_item('padding_top'));
        $padding_bottom = esc_html($this->setting_item('padding_bottom'));

        $data = [
            'title'=> $title,
            'button_text'=> $button_text,
            'campaign'=> $campaign,
            'button_url'=> $button_url,
            'discount'=> $discount,
            'image'=> $image,
            'background_shape'=> $background_shape,
//            'align_image' => $align_image,
            'padding_top'=> $padding_top,
            'padding_bottom'=> $padding_bottom,
             'background_color'=> esc_html($this->setting_item('background_color')),
             'card_color'=> esc_html($this->setting_item('card_color')),
        ];

        return self::renderView('tenant.casual.common.campaign_sale',$data);
    }

    public function enable(): bool
    {
        return (bool) !is_null(tenant());
    }

    public function addon_title()
    {
        return __('Casual: Campaign');
    }
}
