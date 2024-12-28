<?php

namespace Plugins\PageBuilder\Addons\Tenants\Electro\Common;

use App\Helpers\SanitizeInput;
use Plugins\PageBuilder\Fields\Repeater;
use Plugins\PageBuilder\Helpers\RepeaterField;
use Plugins\PageBuilder\PageBuilderBase;
use Plugins\PageBuilder\Fields\ColorPicker;
class Services extends PageBuilderBase
{

    public function preview_image()
    {
        return 'Tenant/common/faq-001.png';
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
            'id' => 'services_repeater',
            'fields' => [
                [
                    'type' => RepeaterField::ICON_PICKER,
                    'name' => 'repeater_icon',
                ],
                [
                    'type' => RepeaterField::TEXT,
                    'name' => 'repeater_title',
                    'label' => __('Title')
                ],
                [
                    'type' => RepeaterField::TEXT,
                    'name' => 'repeater_subtitle',
                    'label' => __('Subtitle')
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
        $padding_top = SanitizeInput::esc_html($this->setting_item('padding_top'));
        $padding_bottom = SanitizeInput::esc_html($this->setting_item('padding_bottom'));
        $repeater_data = $this->setting_item('services_repeater');

        $data = [
            'padding_top' => $padding_top,
            'padding_bottom' => $padding_bottom,
            'repeater_data' => $repeater_data,
             'background_color'=> esc_html($this->setting_item('background_color')),
        ];

        return self::renderView('tenant.electro.common.services', $data);

    }

    public function enable(): bool
    {
        return (bool) !is_null(tenant());
    }

    public function addon_title()
    {
        return __('Electro: Our Services');
    }
}
