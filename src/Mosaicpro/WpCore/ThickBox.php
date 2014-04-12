<?php namespace Mosaicpro\WpCore;

class ThickBox
{
    protected $id = 'the-thickbox-id';
    protected $label = 'Open ThickBox';
    protected $content = 'The ThickBox Content';
    protected $url = 'admin-ajax.php';
    protected $url_data = '';
    protected $type;

    const TB_TYPE_INLINE = 'TB_inline';
    const TB_TYPE_IFRAME = 'TB_iframe';

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
    }

    public static function register()
    {
        $args = func_get_args();
        $args = $args[0];
        return new static($args);
    }

    private function thickbox()
    {
        add_thickbox();
        if ($this->type === self::TB_TYPE_IFRAME)
        {
            $this->url .= '?' . build_query($this->url_data);
            $this->url_data = '';
        }
        else $this->url_data = '&inlineId=' . $this->id;

        $output = '<a href="' . $this->url . '#' . $this->type . '?width=600&height=550' . $this->url_data . '" class="button thickbox">' . $this->label . '</a>';

        if ($this->type === self::TB_TYPE_INLINE)
            $output .= '
            <div id="' . $this->id . '" style="display:none;">
                <p>' . $this->content . '</p>
            </div>';

        return $output;
    }

    public static function get_inline($id, $label = false, $content = false)
    {
        $return = [
            'id' => $id
        ];
        if ($label) $return['label'] = $label;
        if ($content) $return['content'] = $content;
        return $return;
    }

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

    public static function register_iframe()
    {
        $args = func_get_args();
        $config = forward_static_call_array(['self', 'get_iframe'], $args);
        return forward_static_call_array(['self', 'register'], [$config]);
    }

    public static function register_inline()
    {
        $args = func_get_args();
        $config = forward_static_call_array(['self', 'get_inline'], $args);
        return forward_static_call_array(['self', 'register'], [$config]);
    }

    public function render()
    {
        return $this->thickbox();
    }

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

    public static function getFooter()
    {
        wp_footer();
        ?>
        </body>
        </html>
    <?php
    }
}