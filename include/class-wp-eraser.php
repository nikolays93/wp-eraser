<?php

class WP_Eraser
{
    /**
     * @todo erase thumbnails (and @see https://wordpress.org/plugins/sf-taxonomy-thumbnail/)
     * @todo erase term_relationships (@see https://codex.wordpress.org/Function_Reference/wp_delete_object_term_relationships)
     *       or DELETE FROM term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM posts)
     * @todo erase term_taxonomy
     * @todo erase woocommerce_termmeta
     * @todo erase old revisions (@see http://streletzcoder.ru/napisanie-svoego-plagina-dlya-wordpress-chast-3-rabota-s-bazoy-dannyih/)
     * @see At leisure https://wordpress.org/plugins/advanced-database-cleaner/
     */
    const SECURE = 'secret';

    function __construct(){}

    public static function init()
    {
        add_action( 'admin_enqueue_scripts', array(__CLASS__, 'enqueue_resourses'), 99 );
        add_action( 'wp_ajax_erase_posts', array(__CLASS__, 'delete_posts') );
        add_action( 'wp_ajax_erase_terms', array(__CLASS__, 'delete_terms') );
    }

    /**
     * Подключить скрипт с AJAX запросами
     *
     * @access private
     */
    static function enqueue_resourses()
    {
        $src = plugins_url( basename(ERASER_DIR) );
        wp_enqueue_style( 'eraser_style', $src . '/resourse/queries.css' );
        wp_enqueue_script( 'eraser_queries', $src . '/resourse/eraser_page.js', '', '1', true );
        wp_localize_script('eraser_queries', 'eraser_props', array( 'nonce' => wp_create_nonce( self::SECURE ) ) );
    }

    /**
     * Удалить записи
     *
     * @param  string  $post_type
     * @param  array   $args  @see second param in wp_parse_args
     * @return integer $i     count of deletions
     *
     * @access private
     */
    function delete_posts( $post_type, $args = array() )
    {
        $args = wp_parse_args( $args, array(
            'count' => 100,
            ) );

        if( wp_is_ajax() ) {
            if( !isset($_POST['nonce']) || ! wp_verify_nonce( $_POST['nonce'], self::SECURE ) ){
                WPAdminPage::ajax_answer('Ошибка! нарушены правила безопасности');
            }

            if( ! isset($_POST['post_type']) || ! in_array(sanitize_text_field($_POST['post_type']), get_post_types()) ) {
               WPAdminPage::ajax_answer('Неверный тип записи');
            }

            if( isset($_POST['count']) ) {
                $args['count'] = absint($_POST['count']);
            }
        }
        else {
            if( ! in_array(sanitize_text_field($post_type), get_post_types()) ) {
                return false;
            }
        }

        $products = get_posts( array(
            'posts_per_page'   => $args['count'],
            'post_type'        => $post_type,
            ) );

        $i = 0;
        foreach ( $products as $product ) {
            $metas = get_post_meta( $product->ID );
            if( is_array($metas) ) {
                foreach ($metas as $meta_key => $meta_val) {
                    delete_post_meta( $product->ID, $meta_key );
                }
            }

            wp_delete_post( $product->ID, 1 );
            $i++;
            if( $i >= $args['count'] ) break;
        }

        if ( wp_is_ajax() ) {
            WPAdminPage::ajax_answer($i . ' записей удалено. Мета данные очищены.', 1);
        }
        return $i;
    }

    /**
     * Удалить термины
     *
     * @param  string  $tax   register taxanomy
     * @param  array   $args  @see second param in wp_parse_args
     * @return integer $i     count of deletions
     *
     * @access private
     */
    function delete_terms( $tax = false, $args = array() )
    {
        WPAdminPage::ajax_answer('ТЕСТ!');
        $args = wp_parse_args( $args, array(
            'count' => 100,
            ) );

        if( wp_is_ajax() ) {
            if( !isset($_POST['nonce']) || ! wp_verify_nonce( $_POST['nonce'], self::SECURE ) ){
                WPAdminPage::ajax_answer('Ошибка! нарушены правила безопасности');
            }

            if( ! isset($_POST['tax']) || ! in_array(sanitize_text_field($_POST['tax']), get_taxonomies()) ) {
                WPAdminPage::ajax_answer('Неверная таксаномия');
            }

            if( isset($_POST['count']) ) {
                $args['count'] = absint($_POST['count']);
            }
        }
        else {
            if( ! in_array($tax, get_taxonomies()) ) {
                return false;
            }
        }

        $terms = get_terms( $tax, array(
            'fields' => 'ids',
            'hide_empty' => false,
            ) );

        $i = 0;
        foreach ( $terms as $term_id ) {
            $metas = get_term_meta( $term_id );
            if( is_array($metas) ) {
                foreach ($metas as $meta_key => $meta_val) {
                    if( in_array($tax, array('product_tag', 'product_cat')) ) {
                        if( !function_exists('delete_woocommerce_term_meta') )
                            WPAdminPage::ajax_answer('Для удаления терминов товаров включите WooCoomerce');

                        delete_woocommerce_term_meta( $term_id, $meta_key );
                    }
                    else {
                        delete_term_meta( $term_id, $meta_key );
                    }
                }
            }

            wp_delete_term( $term_id, $tax );
            $i++;
            if( $i >= $args['count'] ) break;
        }

        if ( wp_is_ajax() ) {
            WPAdminPage::ajax_answer($i . ' терминов удалено с доп. записями', 1);
        }
        return $i;
    }
}
