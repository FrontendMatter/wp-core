<?php namespace Mosaicpro\WpCore;

use Mosaicpro\Alert\Alert;
use Mosaicpro\Button\Button;
use Mosaicpro\Table\Table;

class CRUD
{
    protected $prefix;
    protected $post;
    protected $related;
    protected $list_fields;
    protected $instance;

    public function __construct($prefix, $post, $related)
    {
        $this->prefix = $prefix;
        $this->post = $post;
        $this->related = $related;
        $this->instance = 'crud_related_instance_' . $this->related;
        return $this;
    }

    public function setListFields($fields)
    {
        $this->list_fields = $fields;
        return $this;
    }

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

    private function handle_ajax_list_related()
    {
        add_action('wp_ajax_' . $this->prefix . '_list_' . $this->related, function()
        {
            wp_enqueue_script('ajax_list_' . $this->related, plugin_dir_url(__FILE__) . 'js/crud/ajax_list.js', ['jquery'], '1.0', true);
            ThickBox::getHeader();

            $related_type = $this->prefix . '_' . $this->related;
            if ($this->related == 'post') $related_type = $this->related;

            $related_posts = get_posts([
                'post_type' => $related_type,
                'numberposts' => -1,
                'post_status' => get_post_stati()
            ]);

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
                        elseif (isset($related_post->{$field}))
                            $related_table_row[$field] = $related_post->{$field};
                        else
                            $related_table_row[$field] = $value;
                    }

                   $related_table_row['actions'] = Button::regular('<i class="glyphicon glyphicon-plus"></i> Add to ' . $this->post)
                        ->isSm()
                        ->addAttributes([
                            'data-toggle' => 'add-to-post',
                            'data-related-id' => $related_post->ID,
                            'data-related-title' => $related_post->post_title,
                            'data-related-instance' => $this->instance
                        ]);

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
                <h3>Edit Unit</h3>
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

    public static function make($prefix, $post, $related)
    {
        return new static($prefix, $post, $related);
    }
} 