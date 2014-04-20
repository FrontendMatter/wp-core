<?php namespace Mosaicpro\WpCore;

use Mosaicpro\Core\IoC;

/**
 * Class ThickBox
 * @package Mosaicpro\WpCore
 */
class ThickBox
{
    /**
     * Holds the ThickBox unique identifier on the current page
     * @var string
     */
    protected $id = 'the-thickbox-id';

    /**
     * Holds the label of the button that opens the ThickBox
     * @var string
     */
    protected $label = 'Open ThickBox';

    /**
     * Holds the inline ThickBox content
     * @var string
     */
    protected $content = 'The ThickBox Content';

    /**
     * Holds the URL to open when using the iframe ThickBox
     * @var string
     */
    protected $url = 'admin-ajax.php';

    /**
     * Holds the data passed to the URL when using the iframe ThickBox
     * @var string
     */
    protected $url_data = '';

    /**
     * Holds the ThickBox type
     * @var string
     */
    protected $type;

    /**
     * Holds the Html utility
     * @var mixed
     */
    protected $html;

    /**
     * Holds the button attributes
     * @var array
     */
    protected $button_attributes = ['class' => 'button thickbox'];

    /**
     * Holds the inline ThickBox type name
     */
    const TB_TYPE_INLINE = 'TB_inline';

    /**
     * Holds the iframe ThickBox type name
     */
    const TB_TYPE_IFRAME = 'TB_iframe';

    /**
     * Create a new ThickBox instance
     */
    public function __construct()
    {
        $args = func_get_args();
        $args = $args[0];
        extract($args);

        if (!isset($id))
            return;

        $this->id = $id;
        if (isset($label)) $this->label = $label;
        if (isset($content)) $this->content = $content;

        if (isset($iframe)) $this->type = self::TB_TYPE_IFRAME;
        else $this->type = self::TB_TYPE_INLINE;

        if (isset($url)) $this->url = $url;
        if (isset($url_data)) $this->url_data = $url_data;

        $this->html = IoC::getContainer('html');
    }

    /**
     * Create a new static ThickBox instance
     * @return static
     */
    public static function register()
    {
        $args = func_get_args();
        $args = $args[0];
        return new static($args);
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setButtonAttributes(array $attributes = [])
    {
        $this->button_attributes = array_merge($this->button_attributes, $attributes);
        return $this;
    }

    /**
     * @return mixed
     */
    private function getButtonAttributes()
    {
        $attributes = $this->button_attributes;
        return $this->html->attributes($attributes);
    }

    /**
     * Create the ThickBox output
     * @return string
     */
    private function thickbox()
    {
        add_action('admin_enqueue_scripts', 'add_thickbox');

        if ($this->type === self::TB_TYPE_IFRAME)
        {
            $this->url .= '?' . build_query($this->url_data);
            $this->url_data = '';
        }
        else $this->url_data = '&inlineId=' . $this->id;

        $output = '<a href="' . $this->url . '#' . $this->type . '?width=600&height=550' . $this->url_data . '"' . $this->getButtonAttributes() . '>' . $this->label . '</a>' . PHP_EOL;

        if ($this->type === self::TB_TYPE_INLINE)
            $output .= '
            <div id="' . $this->id . '" style="display:none;">
                <p>' . $this->content . '</p>
            </div>';

        return $output;
    }

    /**
     * Create a config array for an inline ThickBox
     * @param $id
     * @param bool $label
     * @param bool $content
     * @return array
     */
    public static function get_inline($id, $label = false, $content = false)
    {
        $return = [
            'id' => $id
        ];
        if ($label) $return['label'] = $label;
        if ($content) $return['content'] = $content;
        return $return;
    }

    /**
     * Create a config array for an iframe ThickBox
     * @param $id
     * @param bool $label
     * @param bool $url
     * @param array $data
     * @return array
     */
    public static function get_iframe($id, $label = false, $url = false, array $data = [])
    {
        $return = [
            'id' => $id,
            'iframe' => true,
            'url_data' => $data
        ];
        if ($url) $return['url'] = $url;
        if ($label) $return['label'] = $label;
        return $return;
    }

    /**
     * Create a ThickBox that opens an URL in an iframe
     * @return mixed
     */
    public static function register_iframe()
    {
        $args = func_get_args();
        $config = forward_static_call_array(['self', 'get_iframe'], $args);
        return forward_static_call_array(['self', 'register'], [$config]);
    }

    /**
     * Create an inline ThickBox
     * @return mixed
     */
    public static function register_inline()
    {
        $args = func_get_args();
        $config = forward_static_call_array(['self', 'get_inline'], $args);
        return forward_static_call_array(['self', 'register'], [$config]);
    }

    /**
     * Render the ThickBox output
     * @return string
     */
    public function render()
    {
        return $this->thickbox();
    }

    /**
     * Generate a header for the ThickBox iframe
     */
    public static function getHeader()
    {
        ?>
        <!doctype html>
        <!--[if lt IE 7]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
        <!--[if (IE 7)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8"><![endif]-->
        <!--[if (IE 8)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9"><![endif]-->
        <!--[if gt IE 8]><!--> <html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            <?php wp_head(); ?>
        </head>
        <body>
    <?php
    }

    /**
     * Generate a footer for the ThickBox iframe
     */
    public static function getFooter()
    {
        wp_footer();
        ?>
        </body>
        </html>
    <?php
    }
}