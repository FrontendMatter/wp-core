<?php namespace Mosaicpro\WpCore\Customizer\Controls;

use WP_Customize_Control;

/**
 * Class Page_Template
 * @package Mosaicpro\WpCore\Customizer\Controls
 */
class Page_Template extends WP_Customize_Control
{
    /**
     * Render the control's content
     */
    public function render_content()
    {
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <select name="<?php echo $this->id; ?>" id="<?php echo $this->id; ?>" <?php echo $this->get_link(); ?>>
                <?php
                do_action('post_attributes_metabox');

                echo '<option value="default">Default Template</option>';
                page_template_dropdown();
                ?>
            </select>
        </label>
        <?php
    }
} 