<?php

class EraserAdminPage
{
    const PAGE = 'eraser';

    function __construct()
    {
        $page = new WP_Admin_Page();

        $page->set_args( self::PAGE, array(
            'parent'      => 'options-general.php',
            'title'       => 'Правильное удаление данных',
            'menu'        => 'Ластик',
            'callback'    => array($this, 'page_render'),
            'permissions' => 'manage_options',
            // 'tab_sections'=> null,
            'columns'     => 2,
            ) );

        $page->add_metabox( 'metabox1', 'Стереть записи', array(__CLASS__, 'erase_posts'), $position = 'normal');
        $page->add_metabox( 'metabox2', 'Стереть термины', array(__CLASS__, 'erase_terms'), $position = 'normal');

        $page->add_metabox( 'metabox3', 'Настройки', array(__CLASS__, 'settings'), $position = 'side');
        $page->set_metaboxes();

        add_action( 'wp_ajax_update_existing_posts', array(__CLASS__, 'erase_posts') );
        add_action( 'wp_ajax_update_existing_terms', array(__CLASS__, 'erase_terms') );
    }

    function page_render() {}

    static function erase_posts()
    {
        $types = get_post_types();

        $options = '';
        foreach ($types as $name => $text) {
            $options .= "<option value='{$name}'>" . __($text) . "</option>";
        }

        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';
        $posts = get_posts( array(
            'post_type' => $post_type,
            'post_status' => 'any',
            'posts_per_page' => 4000,
            ) );

        $strPostsTable = '';
        $count = 0;
        if( is_array($posts) && sizeof($posts) >= 1) {
            $strPostsTable .= "<table class='widefat'><tr>";
            foreach ($posts as $post) {
                $strPostsTable .= "<td><label>";
                $strPostsTable .= "<input type='checkbox' value='{$post->ID}'> {$post->post_title}";
                $strPostsTable .= "</label></td> \r\n";
                $count++;
                if( $count % 5 == 0 )
                    $strPostsTable .= "</tr><tr>";
            }
            $strPostsTable .= "</tr></table>";
        }

        if( wp_is_ajax() ) {
            WP_Eraser::ajax_answer($strPostsTable, 1, array('count' => $count) );
        }

        include ERASER_DIR . '/resourse/box-posts.php';
    }
    static function erase_terms()
    {
        $taxes = get_taxonomies();
        $attributes = wc_get_attribute_taxonomies();

        foreach ($attributes as $attribute) {
            unset( $taxes['pa_' . $attribute->attribute_name] );
        }

        $options = '';
        foreach ($taxes as $name => $text) {
            $options .= "<option value='{$name}'>" . __($text) . "</option>";
        }

        $taxanomy = isset($_POST['taxanomy']) ? sanitize_text_field($_POST['taxanomy']) : 'category';
        $args = array(
            'taxanomy'   => $taxanomy,
            'hide_empty' => false,
            // 'number'     => 0,
            );
        $ver = get_bloginfo('version');
        $terms = (version_compare($ver, '4.5', '>=') ) ? get_terms( $args ) : get_terms( $taxanomy, $args );

        $strTermsTable = '';
        $count = 0;
        if( is_array($terms) && sizeof($terms) >= 1) {
            $strTermsTable .= "<table class='widefat'><tr>";
            foreach ($terms as $term) {
                $strTermsTable .= "<td><label>";
                $strTermsTable .= "<input type='checkbox' value='{$term->term_id}'> {$term->name}";
                $strTermsTable .= "</label></td> \r\n";
                $count++;
                if( $count % 5 == 0 )
                    $strTermsTable .= "</tr><tr>";
            }
            $strTermsTable .= "</tr></table>";
        }

        if( wp_is_ajax() ) {
            WP_Eraser::ajax_answer($strTermsTable, 1, array('count' => $count) );
        }

        include ERASER_DIR . '/resourse/box-terms.php';
    }

    static function settings()
    {
        echo "Количетсво записей стираемых за раз.";
    }
}
new EraserAdminPage();
