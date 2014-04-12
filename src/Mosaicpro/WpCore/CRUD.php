<?php namespace Mosaicpro\WpCore;

use Mosaicpro\Alert\Alert;
use Mosaicpro\Button\Button;
use Mosaicpro\ButtonGroup\ButtonGroup;
use Mosaicpro\Table\Table;

/**
 * Class CRUD
 * @package Mosaicpro\WpCore
 */
class CRUD
{
    /**
     * Holds the post prefix
     * @var
     */
    protected $prefix;

    /**
     * Holds the main post type
     * @var
     */
    protected $post;

    /**
     * Holds the Related post type
     * @var
     */
    protected $related;

    /**
     * Holds the Related prefix
     * @var
     */
    protected $related_prefix;

    /**
     * Holds the fields used for composing the Related List table columns
     * @var
     */
    protected $list_fields;

    /**
     * Holds what actions will be displayed in the Related List table
     * @var array
     */
    protected $list_actions = ['edit_related', 'add_to_post'];

    /**
     * Holds custom query arguments for fetching the Related List
     * @var array
     */
    protected $list_query = [];

    /**
     * Holds the CRUD instance ID
     * @var string
     */
    protected $instance;

    /**
     * Create a new CRUD instance
     * @param $prefix
     * @param $post
     * @param $related
     */
    public function __construct($prefix, $post, $related)
    {
        $this->prefix = $prefix;
        $this->post = $post;

        if (is_array($related))
        {
            $this->related_prefix = $related[0];
            $this->related = $related[1];
        }
        else {
            $this->related_prefix = $prefix;
            $this->related = $related;
        }

        $this->instance = 'crud_related_instance_' . $this->related;
        return $this;
    }

    /**
     * Set the fields used for composing the Related List table columns
     * @param $fields
     * @return $this
     */
    public function setListFields($fields)
    {
        $this->list_fields = $fields;
        return $this;
    }

    /**
     * Set the actions to be displayed in the Related List table
     * @param $actions
     * @return $this
     */
    public function setListActions($actions)
    {
        $this->list_actions = $actions;
        return $this;
    }

    /**
     * Set custom query arguments for fetching the Related List
     * @param $query
     * @return $this
     */
    public function setListQuery($query)
    {
        $this->list_query = $query;
        return $this;
    }

    /**
     * Initialize CRUD
     * @return $this
     */
    public function register()
    {
        $this->register_scripts();
        $this->handle_ajax_list_related();
        $this->handle_ajax_edit_related();
        $this->handle_ajax_list_post_related();
        $this->handle_ajax_add_post_related();
        $this->handle_ajax_remove_post_related();
        return $this;
    }

    /**
     * Registers the required scripts in wp admin add/edit pages
     */
    private function register_scripts()
    {
        add_action('admin_enqueue_scripts', function($hook)
        {
            global $post_type;
            global $post;
            if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === $this->prefix . '_' . $this->post)
            {
                $script_id = 'crud_related';
                wp_enqueue_script($script_id, plugin_dir_url(__FILE__) . 'js/crud/related.js', ['jquery'], '1.0', true);
                wp_localize_script(
                    $script_id,
                    'crud_related_instance_' . $this->related,
                    array(
                        'nonce' => wp_create_nonce( $this->prefix . "_" . $this->post . "_nonce" ),
                        'post_id' => $post->ID,
                        'prefix' => $this->prefix,
                        'post' => $this->post,
                        'related' => $this->related,
                    )
                );
            }
        });
    }

    /**
     * Handle Related List AJAX requests
     */
    private function handle_ajax_list_related()
    {
        add_action('wp_ajax_' . $this->prefix . '_list_' . $this->related, function()
        {
            wp_enqueue_script('ajax_list_' . $this->related, plugin_dir_url(__FILE__) . 'js/crud/ajax_list.js', ['jquery'], '1.0', true);
            ThickBox::getHeader();

            $related_type = $this->prefix . '_' . $this->related;
            if (is_null($this->related_prefix)) $related_type = $this->related;

            $related_posts_query = [
                'post_type' => $related_type,
                'numberposts' => -1,
                'post_status' => get_post_stati()
            ];
            $related_posts_query = array_merge($related_posts_query, $this->list_query);
            $related_posts = get_posts($related_posts_query);

            if (count($related_posts) > 0)
            {
                $related_table = [];
                foreach ($related_posts as $related_post)
                {
                    $related_table_row = [];
                    foreach ($this->list_fields as $field => $value)
                    {
                        if (is_numeric($field)) $field = $value;
                        if ($field == 'post_title_permalink')
                        {
                            $related_table_row['title'] = \Mosaicpro\Core\IoC::getContainer('html')
                                ->link(get_permalink($related_post->ID), $related_post->post_title) .
                                '<p>' . wp_trim_words(strip_tags($related_post->post_content)) . '</p>';
                        }
                        elseif ($field == 'post_thumbnail')
                            $related_table_row['Image'] = \Mosaicpro\WpCore\PostList::post_thumbnail($related_post->ID, 50, 50);
                        elseif (is_callable($field))
                        {
                            $callable = $field($related_post);
                            $related_table_row[$callable['field']] = $callable['value'];
                        }
                        elseif (isset($related_post->{$field}))
                            $related_table_row[$field] = $related_post->{$field};
                        else
                            $related_table_row[$field] = $value;
                    }

                    $actions = '';
                    foreach($this->list_actions as $action)
                    {
                        if ($action == 'edit_related')
                        {
                            $actions .= Button::regular('<i class="glyphicon glyphicon-pencil"></i>')
                                ->isXs()
                                ->addUrl(get_admin_url() . 'admin-ajax.php?action=' . $this->prefix . '_edit_' . $this->related . '&related_id=' . $related_post->ID);
                        }
                        if ($action == 'add_to_post')
                        {
                            $actions .= Button::success('<i class="glyphicon glyphicon-plus"></i>')
                                ->isXs()
                                ->addAttributes([
                                    'data-toggle' => 'add-to-post',
                                    'data-related-id' => $related_post->ID,
                                    'data-related-title' => $related_post->post_title,
                                    'data-related-instance' => $this->instance
                                ]);
                        }
                    }
                    $actions = ButtonGroup::make()->add($actions);

                    $related_table_row['actions'] = $actions;
                    $related_table[] = $related_table_row;
                }

                echo Table::make()
                    ->isStriped()
                    ->addBody($related_table, ['actions' => ['class' => 'text-right']]);
            }
            else
                echo Alert::make()->addAlert('No related posts found.')->isInfo();

            ThickBox::getFooter();
            die();
        });
    }

    /**
     * Handle Edit Related AJAX requests
     */
    private function handle_ajax_edit_related()
    {
        add_action('wp_ajax_' . $this->prefix . '_edit_' . $this->related, function()
        {
            $related_id = $_REQUEST['related_id'];
            $is_post = !empty($_POST);
            $related = get_post($related_id);

            if ($is_post)
            {
                check_ajax_referer( $this->prefix . '_' . $this->related . '_nonce', 'nonce' );
                if ( false ) wp_send_json_error( 'Security error' );

                $related_save = [
                    'ID' => $related_id,
                    'post_title' => $_POST['post_title']
                ];
                wp_update_post($related_save);

                $posts = get_posts([
                    'post_type' => $this->prefix . '_' . $this->post,
                    'post_status' => get_post_stati(),
                    'numberposts' => -1
                ]);

                $related_key = $this->prefix . '_' . $this->related;
                foreach($posts as $post_with_related)
                {
                    $list = get_post_meta($post_with_related->ID, $related_key, true);
                    $list = array_set($list, $related_id, ['id' => $related_id, 'title' => $related_save['post_title']]);
                    update_post_meta($post_with_related->ID, $related_key, $list);
                }

                wp_send_json_success();
                die();
            }

            wp_enqueue_script('ajax_edit_related', plugin_dir_url(__FILE__) . 'js/crud/ajax_edit_related.js', ['jquery'], '1.0', true);
            wp_localize_script(
                'ajax_edit_related',
                'related_data',
                array(
                    'nonce' => wp_create_nonce( $this->prefix . "_" . $this->related . "_nonce" ),
                    'related_id' => $related_id
                )
            );

            ThickBox::getHeader();
            ?>
            <div class="col-md-12">
                <h3>Edit <?php echo ucwords($this->related); ?></h3>
            </div>
            <hr/>
            <form action="" class="edit-related-form" data-related-instance="<?php echo $this->instance; ?>" method="post">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label" for="">Title: </label>
                        <input class="form-control" type="text" name="post_title" value="<?php echo esc_attr($related->post_title); ?>" />
                    </div>
                    <?php echo Button::success('Save')->isSubmit()->pullRight(); ?>
                </div>
            </form>

            <?php
            ThickBox::getFooter();
            die();
        });
    }

    /**
     * Handle List Post Related AJAX requests
     */
    private function handle_ajax_list_post_related()
    {
        add_action('wp_ajax_' . $this->prefix . '_list_' . $this->post . '_' . $this->related, function()
        {
            check_ajax_referer( $this->prefix . '_' . $this->post . '_nonce', 'nonce' );

            if ( false ) wp_send_json_error( 'Security error' );

            $post_id = $_POST['post_id'];
            $related_key = $this->prefix . '_' . $this->related;

            $list = get_post_meta($post_id, $related_key, true);
            if (!is_array($list) || empty($list)) $list = [];

            wp_send_json_success( $list );
        });
    }

    /**
     * Handle Add Post Related AJAX requests
     */
    private function handle_ajax_add_post_related()
    {
        add_action('wp_ajax_' . $this->prefix . '_add_' . $this->post . '_' . $this->related, function()
        {
            check_ajax_referer( $this->prefix . '_' . $this->post . '_nonce', 'nonce' );

            if ( false ) wp_send_json_error( 'Security error' );

            $post_id = $_POST['post_id'];
            $related = $_POST['related'];
            $related_key = $this->prefix . '_' . $this->related;

            $list = get_post_meta($post_id, $related_key, true);
            if (!is_array($list)) $list = [];

            $list = array_add($list, $related['id'], $related);
            update_post_meta($post_id, $related_key, $list);

            wp_send_json_success( $list );
        });
    }

    /**
     * Handle Remove Post Related AJAX requests
     */
    private function handle_ajax_remove_post_related()
    {
        add_action('wp_ajax_' . $this->prefix . '_remove_' . $this->post . '_' . $this->related, function()
        {
            check_ajax_referer( $this->prefix . '_' . $this->post . '_nonce', 'nonce' );

            if ( false ) wp_send_json_error( 'Security error' );

            $post_id = $_POST['post_id'];
            $related_id = $_POST['related_id'];
            $related_key = $this->prefix . '_' . $this->related;

            $list = get_post_meta($post_id, $related_key, true);
            if (!is_array($list)) $list = [];

            $list = array_except($list, [$related_id]);
            update_post_meta($post_id, $related_key, $list);

            wp_send_json_success( $list );
        });
    }

    /**
     * Create a new static CRUD instance
     * @param $prefix
     * @param $post
     * @param $related
     * @return static
     */
    public static function make($prefix, $post, $related)
    {
        return new static($prefix, $post, $related);
    }
} 