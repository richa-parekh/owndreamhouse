<?php
namespace aThemes_Addons\Controls;

use Elementor\Base_Data_Control;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Template Link Control
 * 
 * This control displays a link to edit a template that was selected in another control.
 * It connects to a template selection control via the 'connected_option' setting.
 *
 * @since 1.1.0
 */
class Template_Link_Control extends Base_Data_Control {

	public function get_type() {
		return 'aafe-template-link';
	}

	protected function get_default_settings() {
		return [
			'connected_option' => '',
		];
	}

    public function content_template() {
        ?>
        <div class="elementor-control-field">
            <# if ( data.label ) { #>
                <label class="elementor-control-title">{{{ data.label }}}</label>
            <# } #>
    
            <div class="elementor-control-input-wrapper">
                <div class="aafe-template-link-container" data-connected-option="{{ data.connected_option }}">
                    <div class="elementor-control-field-description aafe-template-value">
                        <!-- Template edit button will appear here if template is selected -->
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}