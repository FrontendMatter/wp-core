<?php namespace Mosaicpro\WpCore;

/**
 * Class Taxonomy
 * @package Mosaicpro\WpCore
 */
class Taxonomy
{
    /**
     * Register a Taxonomy
     * @param $name
     * @param $post_type
     * @param array $args
     * @param bool $taxonomy_type
     * @param bool $update_meta_box
     * @return bool
     */
    public static function register($name, $post_type, array $args = [], $taxonomy_type = false, $update_meta_box = false)
    {
        if (!is_array($post_type)) $post_type = [$post_type];
        if (!is_array($name)) $name = [$name, $name . 's'];
        $single = isset($name[0]) ? $name[0] : false;
        $multiple = isset($name[1]) ? $name[1] : false;
        if (!$single || !$multiple) return false;

        $label_single = ucwords($single);
        $label_multiple = ucwords($multiple);
        $slug_single = str_replace(" ", "_", $single);

        if ($taxonomy_type && $taxonomy_type == 'radio')
        {
            // Setup Radio MetaBox Callback
            $args['meta_box_cb'] = function($post, $taxonomy) use ($slug_single)
            {
                return self::radio($post, $taxonomy);
            };
            // Load Radio Taxonomy required scripts
            self::radio_script($slug_single, (is_array($post_type) ? $post_type : [$post_type]));
        }

        $args_default = array(
            'hierarchical' => true,
            'query_var' => $slug_single,
            'labels' => array(
                'name' => $label_multiple,
                'singular_name' => $label_single,
                'edit_item' => 'Edit ' . $label_single,
                'update_item' => 'Update ' . $label_single,
                'add_new_item' => 'Add ' . $label_single,
                'new_item_name' => 'Add New ' . $label_single,
                'all_items' => 'All ' . $label_multiple,
                'search_items' => 'Search ' . $label_multiple,
                'popular_items' => 'Popular ' . $label_multiple,
                'separate_items_with_comments' => 'Separate ' . $label_multiple . ' with commas',
                'add_or_remove_items' => 'Add or remove ' . $label_multiple,
                'choose_from_most_used' => 'Choose from most used ' . $label_multiple
            )
        );
        $args = array_merge($args_default, $args);
        register_taxonomy($slug_single, $post_type, $args);
        register_taxonomy_for_object_type( $slug_single, $post_type );

        // Update Taxonomy meta box
        // Currently supported only by Radio Taxonomies
        if ($update_meta_box && $taxonomy_type == 'radio')
        {
            MetaBox::update($slug_single, $post_type, 'side', $update_meta_box['label'],
                function($post) use ($slug_single) { return self::radio($post, $slug_single); },
                $update_meta_box['context'], $update_meta_box['priority']);
        }
    }

    /**
     * Register multiple Taxonomies
     * @param array $taxonomies
     */
    public static function registerMany(array $taxonomies)
    {
        foreach ($taxonomies as $taxonomy => $args)
        {
            $taxonomy_args = isset($args['args']) ? $args['args'] : [];
            $taxonomy_type = isset($args['taxonomy_type']) ? $args['taxonomy_type'] : false;
            $update_meta_box = isset($args['update_meta_box']) ? $args['update_meta_box'] : false;
            self::register($taxonomy, $args['post_type'], $taxonomy_args, $taxonomy_type, $update_meta_box);
        }
    }

    /**
     * Radio Taxonomy meta_box_cb callback
     * Creates a Radio Taxonomy instead of the default Checkbox
     * @param $post
     * @param $taxonomy
     */
    public static function radio($post, $taxonomy)
    {
        $tax = get_taxonomy($taxonomy);
        $name = 'tax_input[' . $taxonomy . ']';
        $terms = get_terms($taxonomy,array('hide_empty' => 0));

        $postterms = get_the_terms( $post->ID,$taxonomy );
        $current = ($postterms ? array_pop($postterms) : false);
        $current = ($current ? $current->term_id : 0);

        $popular = get_terms( $taxonomy, array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false ) );

        ?>
        <div id="taxonomy-<?php echo $taxonomy; ?>" class="categorydiv">

            <!-- Display tabs-->
            <ul id="<?php echo $taxonomy; ?>-tabs" class="category-tabs">
                <li class="tabs"><a href="#<?php echo $taxonomy; ?>-all" tabindex="3"><?php echo $tax->labels->all_items; ?></a></li>
                <li class="hide-if-no-js"><a href="#<?php echo $taxonomy; ?>-pop" tabindex="3"><?php _e( 'Most Used' ); ?></a></li>
            </ul>

            <!-- Display taxonomy terms -->
            <div id="<?php echo $taxonomy; ?>-all" class="tabs-panel">
                <ul id="<?php echo $taxonomy; ?>checklist" class="list:<?php echo $taxonomy?> categorychecklist form-no-clear">
                    <?php   foreach($terms as $term){
                        $id = $taxonomy.'-'.$term->term_id;
                        echo "<li id='$id'><label class='selectit'>";
                        echo "<input type='radio' data-slug='$term->slug' id='in-$id' name='{$name}'".checked($current,$term->term_id,false).($current == 0 && $term->slug == 'multiple_choice' ? " checked " : "")."value='$term->term_id' />$term->name";
                        echo "</label></li>";
                    }?>
                </ul>
            </div>

            <!-- Display popular taxonomy terms -->
            <div id="<?php echo $taxonomy; ?>-pop" class="tabs-panel" style="display: none;">
                <ul id="<?php echo $taxonomy; ?>checklist-pop" class="categorychecklist form-no-clear" >
                    <?php   foreach($popular as $term){
                        $id = 'popular-'.$taxonomy.'-'.$term->term_id;
                        echo "<li id='$id'><label class='selectit'>";
                        echo "<input type='radio' id='in-$id'".checked($current,$term->term_id,false)."value='$term->term_id' />$term->name<br />";
                        echo "</label></li>";
                    }?>
                </ul>
            </div>

        </div>
        <?php
    }

    /**
     * Enqueue the required Radio Taxonomy scripts for the $taxonomy
     * @param $taxonomy
     * @param array $show_post_types
     * @param array $show_pages
     */
    public static function radio_script($taxonomy, array $show_post_types = [], array $show_pages = ['post.php', 'post-new.php'])
    {
        add_action('admin_enqueue_scripts', function($hook) use ($taxonomy, $show_post_types, $show_pages)
        {
            global $post_type;

            if (in_array($hook, $show_pages, true) && in_array($post_type, $show_post_types, true))
            {
                $script_id = 'taxonomy_radio';
                wp_enqueue_script( $script_id, plugin_dir_url(__FILE__) . 'js/taxonomy/radio.js', array('jquery'), null, true );
                wp_localize_script(
                    $script_id,
                    'taxonomy_radio',
                    array( 'taxonomy' => $taxonomy )
                );
            }
        });
    }
}