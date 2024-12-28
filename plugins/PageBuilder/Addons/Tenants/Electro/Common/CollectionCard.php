<?php

namespace Plugins\PageBuilder\Addons\Tenants\Electro\Common;

use App\Facades\GlobalLanguage;
use App\Helpers\SanitizeInput;
use Plugins\PageBuilder\Fields\Number;
use Plugins\PageBuilder\Fields\Repeater;
use Plugins\PageBuilder\Fields\Select;
use Plugins\PageBuilder\Fields\Text;
use Plugins\PageBuilder\Helpers\RepeaterField;
use Plugins\PageBuilder\PageBuilderBase;
use Plugins\PageBuilder\Fields\ColorPicker;
use function __;

class CollectionCard extends PageBuilderBase
{

    public function preview_image()
    {
           return 'Tenant/common/elecollection_card.png';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();

        $widget_saved_values = $this->get_settings();

        //repeater
        $output .= Repeater::get([
            'multi_lang' => false,
            'settings' => $widget_saved_values,
            'id' => 'repeater_data',
            'fields' => [
                [
                    'type' => RepeaterField::TEXT,
                    'name' => 'repeater_title',
                    'label' => __('Title'),
                    'info' => '<p class="mt-2">'.__('Place {break} if you want to break a sentence. eg, Product {break} Collection ').'</p>'
                ],
                [
                    'type' => RepeaterField::TEXT,
                    'name' => 'repeater_button_text',
                    'label' => __('Button Text')
                ],
                [
                    'type' => RepeaterField::TEXT,
                    'name' => 'repeater_button_url',
                    'label' => __('Button URL')
                ],
                [
                    'type' => RepeaterField::SWITCHER,
                    'name' => 'repeater_button_target',
                    'label' => __('Button Target'),
                    'info' => __('keep on if you want to open the link in a different tab')
                ],
                [
                    'type' => RepeaterField::IMAGE,
                    'name' => 'repeater_image',
                    'label' => __('Image'),
                    'dimensions'=> '320x287 | 320x290 px'
                ],
                [
                    'type' => RepeaterField::COLOR_PICKER,
                    'name' => 'repeater_background_color',
                    'label' => __('Background Color')
                ],
            ]
        ]);
         $output .= ColorPicker::get([
            'name' => 'background_color',
            'label' => __('Background Color'),
            'value' => $widget_saved_values['background_color'] ?? null,
            'info' => '<div class="text-primary mt-1">' . __('Keep empty if you want to keep theme default colors') . '</div>'
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
        $padding_top = esc_html($this->setting_item('padding_top'));
        $padding_bottom = esc_html($this->setting_item('padding_bottom'));

        $repeater = $this->setting_item('repeater_data') ?? '';

        $data = [
            'padding_top' => $padding_top,
            'padding_bottom' => $padding_bottom,
            'repeater' => $repeater,
             'background_color'=> esc_html($this->setting_item('background_color')),
        ];

        return self::renderView('tenant.electro.common.collection-card', $data);
    }

    public function enable(): bool
    {
        return (bool)!is_null(tenant());
    }

    public function addon_title()
    {
        return __('Electro : Collection Card');
    }
}
