<?php

class WP_Eraser
{
    /**
     * @todo erase thumbnails (and @see https://wordpress.org/plugins/sf-taxonomy-thumbnail/)
     * @todo erase old revisions (@see http://streletzcoder.ru/napisanie-svoego-plagina-dlya-wordpress-chast-3-rabota-s-bazoy-dannyih/)
     * @see At leisure delete orphaned relationships (DELETE FROM term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM posts))
     */
    const SECURE = 'secret';

    function __construct(){}

    public static function init()
    {
        add_action( 'admin_enqueue_scripts', array(__CLASS__, 'enqueue_resourses'), 99 );
        add_action( 'wp_ajax_erase_posts', array(__CLASS__, 'delete_posts') );
        add_action( 'wp_ajax_erase_terms', array(__CLASS__, 'delete_terms') );
    }

    static function ajax_answer( $message, $result = 0, $args = array() ) {
        $answer = wp_parse_args( $args, array(
            'result' => $result,
            'message' => $message,
            'count' => 0,
            ) );
        echo json_encode( $answer );
        wp_die();
    }

    /**
     * Подключить скрипт с AJAX запросами
     *
     * @access private
     */
    static function enqueue_resourses()
    {
        $src = plugins_url( basename(ERASER_DIR) );
        wp_enqueue_style( 'eraser_style', $src . '/resourse/style.css' );
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
    static function delete_posts( $post_type, $args = array() )
    {
        global $wpdb;

        $args = wp_parse_args( $args, array(
            'count' => 100,
            ) );

        if( wp_is_ajax() ) {
            if( !isset($_POST['nonce']) || ! wp_verify_nonce( $_POST['nonce'], self::SECURE ) ){
                self::ajax_answer('Ошибка! нарушены правила безопасности');
            }

            if( ! isset($_POST['post_type']) || ! in_array(sanitize_text_field($_POST['post_type']), get_post_types()) ) {
               self::ajax_answer('Неверный тип записи');
            }

            if( isset($_POST['count']) ) {
                $args['count'] = absint($_POST['count']);
            }

            $post_type = $_POST['post_type'];
        }
        else {
            if( ! in_array(sanitize_text_field($post_type), get_post_types()) ) {
                return false;
            }
        }

        $products = get_posts( array(
            // 'post__in' => array('83335'),
            'posts_per_page'   => $args['count'],
            'post_type'        => $post_type,
            ) );

        $i = 0;
        foreach ( $products as $product ) {
            //wp_remove_object_terms( $post_id, $terms, $taxonomy );
            $wpdb->delete( $wpdb->term_relationships, array('object_id' => $product->ID), array('%d') );

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
            self::ajax_answer($i . ' записей удалено. Мета данные очищены.', 1);
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
    static function delete_terms( $tax = false, $args = array() )
    {
        $result_code = 2;
        $args = wp_parse_args( $args, array(
            'count' => 20,
            ) );

        if( wp_is_ajax() ) {
            if( !isset($_POST['nonce']) || ! wp_verify_nonce( $_POST['nonce'], self::SECURE ) ){
                self::ajax_answer('Ошибка! нарушены правила безопасности');
            }

            if( ! isset($_POST['tax']) || ! in_array($_POST['tax'], get_taxonomies()) ) {
                self::ajax_answer('Неверная таксаномия');
            }

            if( isset($_POST['count']) ) {
                $args['count'] = absint($_POST['count']);
            }

            $tax = sanitize_text_field($_POST['tax']);
        }
        else {
            if( ! in_array($tax, get_taxonomies()) ) {
                return false;
            }
        }

        $term_args = array(
            'fields' => 'ids',
            'taxanomy'   => $tax,
            'hide_empty' => false,
            'number'     => $args['count'],
            'include'    => array(),
            'exclude'    => array(42466, 42465, 42464, 10084, 16838, 42471, 16762, 42462, 10174, 10174),
            );
        $ver = get_bloginfo('version');
        $terms = (version_compare($ver, '4.5', '>=') ) ? get_terms( $term_args ) : get_terms( $tax, $term_args );

        if( is_wp_error($terms) ) {
            self::ajax_answer( $terms->get_error_message() );
        }
        elseif( is_array($terms) && sizeof($terms) ) {
            $result_code = 1;
            foreach ( $terms as $term_id ) {
                $metas = get_term_meta( $term_id );
                if( is_array($metas) ) {
                    foreach ($metas as $meta_key => $meta_val) {
                        if( in_array($tax, array('product_tag', 'product_cat')) ) {
                            if( !function_exists('delete_woocommerce_term_meta') )
                                self::ajax_answer('Для удаления терминов товаров включите WooCoomerce');

                            delete_woocommerce_term_meta( $term_id, $meta_key );
                        }
                        else {
                            delete_term_meta( $term_id, $meta_key );
                        }
                    }
                }
                $delete_result = wp_delete_term( $term_id, $tax );

                if( is_wp_error($delete_result) ) {
                    self::ajax_answer( $delete_result->get_error_message() );
                }
            }
        }

        if ( wp_is_ajax() ) {
            self::ajax_answer($args['count'] . ' терминов удалено с доп. записями', 1, array('count' => $args['count']));
        }
        return $i;
    }
}
