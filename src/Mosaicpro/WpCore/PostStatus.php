<?php namespace Mosaicpro\WpCore;

/**
 * Class PostStatus
 * @package Mosaicpro\WpCore
 */
class PostStatus
{
    /**
     * Holds the post types for the post status
     * @var array
     */
    protected $post_type;

    /**
     * Holds the post status
     * @var
     */
    protected $status;

    /**
     * Holds the post status label
     * @var string|void
     */
    protected $label;

    /**
     * Holds the post status label count
     * @var array
     */
    protected $label_count;

    /**
     * Holds the i18n text domain
     * @var
     */
    protected $text_domain;

    /**
     * Create a new PostStatus instance
     * @param $status
     * @param $text_domain
     */
    public function __construct($status, $text_domain)
    {
        $this->post_type = [];
        $this->status = $status;

        // default label
        $this->label = __(
            ucwords( str_replace(
                [ '_', '-' ],
                [ ' ', ' ' ],
                $this->status
            ) ),
            $this->text_domain
        );

        // default label_count
        $this->label_count = _n_noop(
            "{$this->label} <span class='count'>(%s)</span>",
            "{$this->label} <span class='count'>(%s)</span>",
            $this->text_domain
        );

        return $this;
    }

    /**
     * Create a new PostStatus instance statically
     * @param $status
     * @param null $text_domain
     * @return static
     */
    public static function make($status, $text_domain = null)
    {
        return new static($status, $text_domain);
    }

    /**
     * Set the post types for the post status
     * @param $post_type
     * @return $this
     */
    public function setPostType($post_type)
    {
        $this->post_type = $post_type;
        return $this;
    }

    /**
     * Register the post status to WordPress
     * @param array $args
     */
    public function register(array $args = [])
    {
        $args = wp_parse_args($args, [
            'label' => $this->label,
            'label_count' => $this->label_count,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list' => true,
            'public' => false,
            'exclude_from_search' => false
        ]);

        add_action('init', function() use ($args)
        {
            register_post_status($this->status, $args);
        });

        add_action( 'admin_enqueue_scripts', array( $this,'post_status_metabox' ) );
        add_action( 'admin_enqueue_scripts', array( $this,'post_status_quick_edit' ) );
    }

    /**
     * Display the custom post status on the new/edit page metabox
     * @param $hook
     * @return bool
     */
    public function post_status_metabox($hook)
    {
        if (!in_array($hook, ['post.php', 'post-new.php']))
            return false;

        if (!$this->allowPostType())
            return false;

        $dropdown = $this->getDropdown();
        $script_id = 'mp-post-status-metabox';
        wp_enqueue_script($script_id, plugin_dir_url(__FILE__) . 'js/post-status/metabox.js', ['jquery'], '1.0', true);

        global $wp_scripts;
        $data = $wp_scripts->get_data($script_id, 'data');

        if(empty($data))
        {
            wp_localize_script(
                $script_id,
                'post_status_metabox',
                array(
                    'display' => $dropdown['display'],
                    'options' => $dropdown['options']
                )
            );
        }
    }

    /**
     * Display the custom post status in the quick and bulk edit screens
     * @param $hook
     * @return bool
     */
    public function post_status_quick_edit($hook)
    {
        if ($hook !== 'edit.php')
            return false;

        if (!$this->allowPostType())
            return false;

        $dropdown = $this->getDropdown();
        $script_id = 'mp-post-status-quick';
        wp_enqueue_script($script_id, plugin_dir_url(__FILE__) . 'js/post-status/quick-edit.js', ['jquery'], '1.0', true);

        global $wp_scripts;
        $data = $wp_scripts->get_data($script_id, 'data');

        if(empty($data))
        {
            wp_localize_script(
                $script_id,
                'post_status_quick',
                array( 'options' => $dropdown['options'] )
            );
        }
    }

    /**
     * Performs a check against the current screen's post type and the post types of the post status
     * @return bool
     */
    private function allowPostType()
    {
        if (empty($this->post_type))
            return false;

        global $post_type;

        if (is_array($this->post_type))
        {
            if (!in_array($post_type, $this->post_type))
                return false;
        }
        elseif ($post_type !== $this->post_type)
            return false;

        return true;
    }

    /**
     * Create the post status dropdown options
     * @return array
     */
    private function getDropdown()
    {
        global $wp_post_statuses, $post;

        // Get all non-builtin post status and add them as <option>
        $options = $display = '';
        foreach ( $wp_post_statuses as $status )
        {
            if ( ! $status->_builtin )
            {
                // Match against the current posts status
                $selected = selected( $post->post_status, $status->name, false );

                // If one of our custom post status is selected, remember it
                $selected AND $display = $status->label;

                // Build the options
                $options .= "<option{$selected} value='{$status->name}'>{$status->label}</option>";
            }
        }

        return ['options' => $options, 'display' => $display];
    }
}