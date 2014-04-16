<?php namespace Mosaicpro\WpCore;

use Mosaicpro\Alert\Alert;
use Mosaicpro\Button\Button;
use Mosaicpro\ButtonGroup\ButtonGroup;
use Mosaicpro\ListGroup\ListGroup;
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
    protected $list_fields = ['default' => ['ID', 'post_title']];

    /**
     * Holds the fields used for composing the Post Related List table columns
     * @var array
     */
    protected $post_related_list_fields = ['default' => ['ID', 'post_title']];

    /**
     * Holds what actions will be displayed in the Related List table
     * @var array
     */
    protected $list_actions = ['default' => ['edit_related', 'add_to_post']];

    /**
     * Holds the post related list actions / buttons
     * @var array
     */
    protected $post_related_list_actions = ['default' => ['edit_related_thickbox', 'remove_from_post']];

    /**
     * Holds custom query arguments for fetching the Related List
     * @var array
     */
    protected $list_query = [];

    /**
     * Holds the related list format
     * @var string
     */
    protected $list_related_format = 'table';

    /**
     * Holds the post related list format
     * @var string
     */
    protected $list_post_related_format = 'table';

    /**
     * Holds the CRUD instance ID
     * @var string
     */
    protected $instance;

    /**
     * Holds whether CRUD is mixed / contains multiple related post types
     * @var bool
     */
    protected $mixed;

    /**
     * Holds the post types options
     * Used when CRUD handles the creation of post types
     * @var array
     */
    protected $post_type_options = [];

    /**
     * @return mixed|string
     */
    private function getRelatedId()
    {
        return is_array($this->getRelated()) ? implode('_', $this->getRelated()) : $this->getRelated();
    }

    /**
     * Create a new CRUD instance
     * @param $prefix
     * @param $post
     * @param $related
     */
    public function __construct($prefix, $post, $related)
    {
        $this->post = $post;
        $this->prefix = $prefix;
        $this->mixed = is_array($related);

        if ($this->mixed)
        {
            foreach ($related as $related_item)
            {
                if (is_array($related_item))
                {
                    $this->related_prefix[$related_item[1]] = $related_item[0];
                    $this->setListFields($related_item[0] . $related_item[1], $this->list_fields['default']);
                    $this->setListActions($related_item[0] . $related_item[1], $this->list_actions['default']);
                    $this->setPostRelatedListActions($related_item[0] . $related_item[1], $this->post_related_list_actions['default']);
                    $this->related[] = $related_item[1];
                }
                else {
                    $this->related_prefix[$related_item] = $prefix;
                    $this->setListFields($prefix . '_' . $related_item, $this->list_fields['default']);
                    $this->setListActions($prefix . '_' . $related_item, $this->list_actions['default']);
                    $this->setPostRelatedListActions($prefix . '_' . $related_item, $this->post_related_list_actions['default']);
                    $this->related[] = $related_item;
                }
            }
        }
        else
        {
            $this->related = $related;
            $this->related_prefix[$related] = $prefix;
            $this->setListFields($prefix . '_' . $related, $this->list_fields['default']);
            $this->setListActions($prefix . '_' . $related, $this->list_actions['default']);
            $this->setPostRelatedListActions($prefix . '_' . $related, $this->post_related_list_actions['default']);
        }

        if ($this->mixed) $this->instance = 'crud_related_instance_mixed_' . $this->getRelatedId();
        else $this->instance = 'crud_related_instance_' . $related;

        $this->setPostTypeOptions('default', ['args' => ['show_in_menu' => $prefix]]);

        return $this;
    }

    /**
     * Handle the creation of post types
     */
    private function setup_post_types()
    {
        $types = $this->getRelatedType();
        if (!is_array($types)) $types = [$types];
        array_unshift($types, $this->prefix . '_' . $this->post);

        $default_options = $this->getPostTypeOptions();
        foreach($types as $type)
        {
            if (post_type_exists($type))
                continue;

            $options = $this->getPostTypeOptions($type);
            $options = array_merge($default_options, $options);

            $type = str_replace($this->prefix . "_", "", $type);

            $name = isset($options['name']) ? $options['name'] : $type;
            $args = isset($options['args']) ? $options['args'] : [];

            PostType::register($this->prefix, $name, $args);
        }
    }

    /**
     * Get post type options
     * Returns the 'default' array if post_type was not specified
     * Used when CRUD handles the creation of post types
     * @param string $post_type
     * @return array
     */
    public function getPostTypeOptions($post_type = 'default')
    {
        return isset($this->post_type_options[$post_type]) ? $this->post_type_options[$post_type] : [];
    }

    /**
     * Set the post type options for $post_type
     * Used when CRUD handles the creation of post types
     * @param $post_type
     * @param $args
     * @return $this
     */
    public function setPostTypeOptions($post_type, $args)
    {
        $this->post_type_options[$post_type] = array_merge($this->getPostTypeOptions($post_type), $args);
        return $this;
    }

    /**
     * Set the fields used for composing the Related List table columns;
     * By default, the Post Related List (Related Meta Box) will copy this format;
     * @param $related
     * @param $fields
     * @return $this
     */
    public function setListFields($related, $fields)
    {
        $this->list_fields[$related] = $fields;
        $this->post_related_list_fields[$related] = $fields;
        return $this;
    }

    /**
     * Returns the stored list_fields
     * @return mixed
     */
    public function getListFields()
    {
        return $this->list_fields;
    }

    /**
     * Returns the stored instance
     * @return mixed
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Returns the stored related
     * @return mixed
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Set the post related list fields
     * Can also accept a $fields closure that allows to copy the list_fields
     * e.g. setPostRelatedListFields('my_post_type', function($instance){
     *      return $instance->getListFields();
     * });
     * @param $related
     * @param $fields
     * @return $this
     */
    public function setPostRelatedListFields($related, $fields)
    {
        $this->post_related_list_fields[$related] = is_callable($fields) ? $fields($this) : $fields;
        return $this;
    }

    /**
     * Set the actions to be displayed in the Related List table
     * @param $related
     * @param $actions
     * @return $this
     */
    public function setListActions($related, $actions)
    {
        $this->list_actions[$related] = $actions;
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
     * @param string $list_related_format
     * @return $this
     */
    public function setListRelatedFormat($list_related_format)
    {
        $this->list_related_format = $list_related_format;
        return $this;
    }

    /**
     * @param string $list_post_related_format
     * @return $this
     */
    public function setListPostRelatedFormat($list_post_related_format)
    {
        $this->list_post_related_format = $list_post_related_format;
        return $this;
    }

    /**
     * @param $related
     * @param array $post_related_list_actions
     * @return $this
     */
    public function setPostRelatedListActions($related, $post_related_list_actions)
    {
        $this->post_related_list_actions[$related] = $post_related_list_actions;
        return $this;
    }

    /**
     * Return the Related post type with or without the Related prefix
     * @param null $related
     * @return string
     */
    private function getRelatedType($related = null)
    {
        if (is_null($related)) $related = $this->getRelated();
        if ($this->mixed)
        {
            if (!is_array($related)) $related = [$related];

            $related_type_list = [];
            foreach ($related as $related_item)
            {
                $related_prefix = '';
                if (!empty($this->related_prefix[$related_item])) $related_prefix = $this->related_prefix[$related_item] . '_';
                $related_type = $related_prefix . $related_item;
                $related_type_list[] = $related_type;
            }
            return $related_type_list;
        }
        else
        {
            $related_prefix = '';
            if (!empty($this->related_prefix[$related])) $related_prefix = $this->related_prefix[$related] . '_';
            $related_type = $related_prefix . $related;
        }

        return $related_type;
    }

    /**
     * Apply filter for post type name label
     * @param $related_item
     * @return mixed|void
     */
    public static function getPostTypeLabel($related_item)
    {
        return apply_filters('crud_post_type_label_' . $related_item, $related_item);
    }

    /**
     * Add filter for post type name label
     * @param $post_type
     * @param $label
     */
    public static function setPostTypeLabel($post_type, $label)
    {
        add_filter('crud_post_type_label_' . $post_type, function($label) use ($label)
        {
            return $label;
        });
    }

    /**
     * Initialize CRUD
     * @return $this
     */
    public function register()
    {
        $this->setup_post_types();
        $this->register_scripts();
        $this->handle_ajax_list_related();
        $this->handle_ajax_edit_related();
        $this->handle_ajax_list_post_related();
        $this->handle_ajax_list_post_related_mixed();
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
                    $this->getInstance(),
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
        $related_list = [$this->getRelated()];
        if ($this->mixed) $related_list = $this->getRelated();

        foreach($related_list as $related)
        {
            add_action('wp_ajax_' . $this->prefix . '_list_' . $related, function($related) use ($related)
            {
                wp_enqueue_script('ajax_list_' . $related, plugin_dir_url(__FILE__) . 'js/crud/ajax_list.js', ['jquery'], '1.0', true);
                ThickBox::getHeader();

                $related_posts_query = [
                    'post_type' => $this->getRelatedType($related),
                    'numberposts' => -1
                ];
                $related_posts_query = array_merge($related_posts_query, $this->list_query);
                $related_posts = get_posts($related_posts_query);

                if (count($related_posts) > 0)
                    echo $this->get_list_format($this->list_related_format, $related_posts);
                else
                    echo Alert::make()->addAlert('No related posts found (' . $this->getRelatedType($related) . ').')->isInfo();

                ThickBox::getFooter();
                die();
            });
        }
    }

    /**
     * Handle Edit Related AJAX requests
     */
    private function handle_ajax_edit_related()
    {
        $related_list = [$this->getRelatedType()];
        if ($this->mixed) $related_list = $this->getRelatedType();

        foreach($related_list as $related_item)
        {
            add_action('wp_ajax_' . $this->prefix . '_edit_' . $related_item, function($related_item) use ($related_item)
            {
                $related_id = $_REQUEST['related_id'];
                $is_post = !empty($_POST);

                if ($is_post)
                {
                    check_ajax_referer( $this->prefix . '_' . $related_item . '_nonce', 'nonce' );
                    if ( false ) wp_send_json_error( 'Security error' );

                    $related_save = [
                        'ID' => $related_id,
                        'post_title' => $_POST['post_title']
                    ];
                    wp_update_post($related_save);
                    wp_send_json_success();
                    die();
                }

                $related = get_post($related_id);

                wp_enqueue_script('ajax_edit_related', plugin_dir_url(__FILE__) . 'js/crud/ajax_edit_related.js', ['jquery'], '1.0', true);
                wp_localize_script(
                    'ajax_edit_related',
                    'related_data',
                    array(
                        'nonce' => wp_create_nonce( $this->prefix . "_" . $related_item . "_nonce" ),
                        'action' => $this->prefix . '_edit_' . $related_item,
                        'related_id' => $related_id
                    )
                );

                ThickBox::getHeader();
                ?>
                <div class="col-md-12">
                    <h3>Edit <?php echo $this->getPostTypeLabel($related_item); ?></h3>
                </div>
                <hr/>
                <form action="" class="edit-related-form" data-related-instance="<?php echo $this->getInstance(); ?>" method="post">
                    <div class="col-md-12">
                        <?php FormBuilder::input('post_title', 'Title', esc_attr($related->post_title)); ?>
                        <?php do_action('crud_' . $this->prefix . '_edit_' . $related_item . '_form', $related, $this); ?>
                        <?php echo Button::success('Save')->isSubmit()->pullRight(); ?>
                        <?php echo Button::link('Go to full edit page')->addUrl(get_edit_post_link($related->ID))->addAttributes(['target' => '_parent'])->pullRight(); ?>
                    </div>
                </form>
                <?php
                ThickBox::getFooter();
                die();
            });
        }
    }

    /**
     * Handle List Post Related AJAX requests
     */
    private function handle_ajax_list_post_related()
    {
        if (is_array($this->getRelated()))
            return false;

        add_action('wp_ajax_' . $this->prefix . '_list_' . $this->post . '_' . $this->getRelated(), function()
        {
            check_ajax_referer( $this->prefix . '_' . $this->post . '_nonce', 'nonce' );

            if ( false ) wp_send_json_error( 'Security error' );

            $post_id = $_POST['post_id'];
            $related_key = $this->prefix . '_' . $this->getRelated();

            $list = get_post_meta($post_id, $related_key, true);
            if (!is_array($list) || empty($list)) $list = [];

            $list_ids = [];
            foreach($list as $list_item)
                $list_ids[] = $list_item['id'];

            if (empty($list_ids))
                return wp_send_json_success();

            $related_posts = get_posts([
                'post_type' => $this->getRelatedType(),
                'numberposts' => -1,
                'post__in' => $list_ids
            ]);

            $list_format = $this->get_list_format($this->list_post_related_format, $related_posts, 'post_related_');
            wp_send_json_success( $list_format );
        });
    }

    /**
     * Handle List Post Related Mixed AJAX requests
     */
    private function handle_ajax_list_post_related_mixed()
    {
        if (!is_array($this->getRelated()))
            return false;

        add_action('wp_ajax_' . $this->prefix . '_list_' . $this->post . '_' . $this->getRelatedId(), function()
        {
            check_ajax_referer( $this->prefix . '_' . $this->post . '_nonce', 'nonce' );

            if ( false ) wp_send_json_error( 'Security error' );

            $post_id = $_POST['post_id'];
            $related_key = $this->prefix . '_mixed';

            $list = get_post_meta($post_id, $related_key, true);
            if (!is_array($list) || empty($list)) $list = [];

            $list_ids = [];
            foreach($list as $list_item)
                $list_ids[] = $list_item['id'];

            $list_ids = array_unique($list_ids);
            if (empty($list_ids))
                return wp_send_json_success();

            $related_posts = get_posts([
                'post_type' => $this->getRelatedType(),
                'numberposts' => -1,
                'post__in' => $list_ids
            ]);

            $list_format = $this->get_list_format($this->list_post_related_format, $related_posts, 'post_related_');
            wp_send_json_success( $list_format );
        });
    }

    /**
     * Forwards a posts list to the right format method
     * @param $format_key
     * @param $related_posts
     * @param string $prefix
     * @return mixed
     */
    private function get_list_format($format_key, $related_posts, $prefix = '')
    {
        if (is_callable($format_key))
            return $format_key($related_posts);

        return $this->{"get_list_format_" . $format_key}($related_posts, $prefix);
    }

    /**
     * Formats a list row data for output
     * @param $related_post
     * @param $fields
     * @return array
     */
    private function get_list_format_row($related_post, $fields)
    {
        $related_table_row = [];
        foreach ($fields as $field => $value)
        {
            if (is_numeric($field)) $field = $value;
            $field_label = !is_callable($field) ? ucwords(str_replace("_", " ", $field)) : '';
            if ($field == 'post_title_permalink')
            {
                $related_table_row['Title'] = \Mosaicpro\Core\IoC::getContainer('html')
                        ->link(get_permalink($related_post->ID), $related_post->post_title) .
                    '<p>' . wp_trim_words(strip_tags($related_post->post_content)) . '</p>';
            }
            elseif ($field == 'post_thumbnail')
                $related_table_row['Image'] = PostList::post_thumbnail($related_post->ID, 50, 50);
            elseif (starts_with($field, 'crud_edit_'))
            {
                $parts = explode('crud_edit_', $field);
                $field_label = ucwords(str_replace("_", " ", $parts[1]));
                $field = $parts[1];
                $related_table_row[$field_label] = Button::link($related_post->{$field})
                    ->isLink()
                    ->addClass('thickbox')
                    ->addAttributes(['title' => 'Edit ' . CRUD::getPostTypeLabel($related_post->post_type)])
                    ->addUrl(admin_url() . 'admin-ajax.php?action=' . $this->prefix . '_edit_' . $related_post->post_type . '&related_id=' . $related_post->ID . '#TB_iframe?width=600&width=550');
            }
            elseif (starts_with($field, 'count_'))
            {
                $parts = explode('count_', $field);
                $field = $parts[1];
                $field_label = self::getPostTypeLabel($field) . '(s)';
                $related_table_row[$field_label] = count($related_post->{$field}) . ' ' . $field_label;
            }
            elseif (starts_with($field, 'yes_no_'))
            {
                $parts = explode('yes_no_', $field);
                $field_label = ucwords(str_replace("_", " ", $parts[1]));
                $field = $parts[1];
                $related_table_row[$field_label] = $related_post->{$field} == 1 ? '<strong>Yes</strong>' : 'No';
            }
            elseif (is_callable($field))
            {
                $callable = $field($related_post);
                $related_table_row[$callable['field']] = $callable['value'];
            }
            elseif (isset($related_post->{$field}))
                $related_table_row[$field_label] = $related_post->{$field};
            else
                $related_table_row[$field_label] = '';
        }
        return $related_table_row;
    }

    /**
     * Format a post list with the ListGroup component
     * @param $related_posts
     * @param string $prefix
     * @return mixed
     */
    private function get_list_format_listgroup($related_posts, $prefix = '')
    {
        $list_wrapper = ListGroup::make();
        foreach($related_posts as $related_post)
        {
            $actions = $this->get_list_actions($prefix . 'list_actions', $related_post);
            $button_group = ButtonGroup::make()->isXs()->pullRight();
            foreach ($actions as $action)
                $button_group->add($action);

            $row = $this->get_list_format_row($related_post, $this->{$prefix . 'list_fields'}[$related_post->post_type]);

            $row_output = '';
            foreach($row as $row_heading => $row_content)
                $row_output .= '<p><strong>' . $row_heading . '</strong>: ' . $row_content . '</p>';

            $list_content = $button_group . $row_output;
            $list_wrapper->addList($list_content);
        }
        return $list_wrapper->__toString();
    }

    /**
     * Format a post list with the Table component
     * @param $related_posts
     * @param string $prefix
     * @return mixed
     */
    private function get_list_format_table($related_posts, $prefix = '')
    {
        $related_table = [];
        $related_table_row_columns = [];
        foreach ($related_posts as $related_post)
        {
            $row = $this->get_list_format_row($related_post, $this->{$prefix . 'list_fields'}[$related_post->post_type]);
            $actions = $this->get_list_actions($prefix . 'list_actions', $related_post);
            $button_group = ButtonGroup::make()->addAttributes(['class' => 'btn-group-xs'])->pullRight();
            foreach ($actions as $action)
                $button_group->add($action);

            $row['Actions'] = $button_group;
            $related_table[] = $row;
        }

        $tableHeader = $this->mixed && $prefix == 'post_related_' ? false : array_keys($related_table[0]);

        return Table::make()
            ->isStriped()
            ->addHeader($tableHeader, ['Actions' => ['class' => 'text-right']])
            ->addBody($related_table, ['Actions' => ['class' => 'text-right']])->__toString();
    }

    /**
     * Compose the list actions / buttons
     * @param $list_key
     * @param $related_post
     * @return array
     */
    private function get_list_actions($list_key, $related_post)
    {
        $actions = [];
        foreach($this->{$list_key}[$related_post->post_type] as $action)
        {
            if ($action == 'edit_related_thickbox')
            {
                $actions[] = Button::regular('<i class="glyphicon glyphicon-pencil"></i>')
                    ->addAttributes(['title' => 'Edit ' . self::getPostTypeLabel($related_post->post_type), 'class' => 'thickbox'])
                    ->addUrl(admin_url() . 'admin-ajax.php?action=' . $this->prefix . '_edit_' . $related_post->post_type . '&related_id=' . $related_post->ID . '#TB_iframe?width=600&width=550');
            }
            if ($action == 'edit_related')
            {
                $actions[] = Button::regular('<i class="glyphicon glyphicon-pencil"></i>')
                    ->addAttributes(['title' => 'Edit ' . self::getPostTypeLabel($related_post->post_type)])
                    ->addUrl(admin_url() . 'admin-ajax.php?action=' . $this->prefix . '_edit_' . $related_post->post_type . '&related_id=' . $related_post->ID . '#TB_iframe?width=600&width=550');
            }
            if ($action == 'add_to_post')
            {
                $actions[] = Button::success('<i class="glyphicon glyphicon-plus"></i>')
                    ->isXs()
                    ->addAttributes([
                        'data-toggle' => 'add-to-post',
                        'data-related-id' => $related_post->ID,
                        'data-related-title' => $related_post->post_title,
                        'data-related-type' => $related_post->post_type,
                        'data-related-instance' => $this->getInstance(),
                        'title' => 'Add ' . self::getPostTypeLabel($related_post->post_type) . ' to ' . $this->post
                    ]);
            }
            if ($action == 'remove_from_post')
            {
                $actions[] = Button::danger('<i class="glyphicon glyphicon-trash"></i>')
                    ->addAttributes([
                        'title' => 'Remove ' . self::getPostTypeLabel($related_post->post_type) . ' from ' . $this->post,
                        'data-toggle' => 'remove-from-post',
                        'data-related-id' => $related_post->ID,
                        'data-related-instance' => $this->getInstance()
                    ]);
            }
            if (is_callable($action))
                $actions[] = $action($related_post);
        }
        return $actions;
    }

    /**
     * Handle Add Post Related AJAX requests
     */
    private function handle_ajax_add_post_related()
    {
        add_action('wp_ajax_' . $this->prefix . '_add_' . $this->post . '_' . $this->getRelatedId(), function()
        {
            check_ajax_referer( $this->prefix . '_' . $this->post . '_nonce', 'nonce' );

            if ( false ) wp_send_json_error( 'Security error' );

            $post_id = $_POST['post_id'];
            $related = $_POST['related'];
            $related_key = $this->prefix . '_' . (is_array($this->getRelated()) ? 'mixed' : $this->getRelatedId());

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
        add_action('wp_ajax_' . $this->prefix . '_remove_' . $this->post . '_' . $this->getRelatedId(), function()
        {
            check_ajax_referer( $this->prefix . '_' . $this->post . '_nonce', 'nonce' );

            if ( false ) wp_send_json_error( 'Security error' );

            $post_id = $_POST['post_id'];
            $related_id = $_POST['related_id'];
            $related_key = $this->prefix . '_' . (is_array($this->getRelated()) ? 'mixed' : $this->getRelatedId());

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

    /**
     * Hook into the Edit Related Form
     * @param $related
     * @param $callback
     * @return $this
     */
    public function setForm($related, $callback)
    {
        add_action('crud_' . $this->prefix . '_edit_' . $related . '_form', $callback);
        return $this;
    }
} 