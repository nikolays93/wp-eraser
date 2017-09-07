<?php

class EraserAdminPage extends WPAdminPage
{

    function __construct()
    {
        parent::__construct( 'eraser', array(
            'parent' => 'options-general.php',
            'title' => __('Отчистить данные'),
            'menu' => __('Ластик'),
            ),
        array($this, 'page_render')
        );

        $this->add_metabox( 'metabox1', 'Стереть записи', array($this, 'erase_posts'), $position = 'normal');
        $this->add_metabox( 'metabox2', 'Стереть термины', array($this, 'erase_terms'), $position = 'normal');
        $this->set_metaboxes();
    }

    function page_render() {}

    function erase_posts()
    {
        self::render_post_type_select();
        submit_button( 'Стереть данные', 'primary' );
        echo "<div class='clear'></div>";
    }

    function erase_terms()
    {
        ?>
        <form action="">
        <?php
        $count = self::render_taxanomies_select(); ?>
            <p class="logout">Всего терминов: <span id='terms_count'><?php echo $count; ?></span></p>
            <button id="erase-terms" class="button button-primary erase">Стереть данные</button>
            <span class="spinner"><!-- is-active --></span>
            <div class='clear'></div>
        </form>
        <?php
    }

    public static function render_post_type_select()
    {
        $post_type = isset($_POST['post_type']) ? $_POST['post_type'] : 'post';
        $types = get_post_types();

        $posts = get_posts( array(
            'post_type' => $post_type,
            ) );
        // var_dump($posts);
        ?>
        <label for='erase_post_type'>Тип записи:</label>
        <p>
        <select name='erase_post_type' id='erase_post_type'>
            <?php
            foreach ($types as $name => $text) {
                echo "<option value='{$name}'>{$text}</option>";
            }
            ?>
        </select>
        </p>
        <div id='posts_wrapper' class='columns'>
            <table class="widefat striped">
            <?php
            // if( is_array($posts) && sizeof($posts) ) {
            //     echo "<tr>";
            //     $i = 0;
            //     foreach ($posts as $post) {
            //         echo "<td><label><input type='checkbox'><span>{$post->post_title}</span></label></td>";
            //         if( $i == 6 ) {
            //             echo "</tr><tr>";
            //         }
            //         $i++;
            //     }
            //     echo "</tr>";
            // }
            ?>
            </table>
        </div>
        <?php
    }

    public static function render_taxanomies_select()
    {
        $taxes = get_taxonomies();
        $attributes = wc_get_attribute_taxonomies();

        foreach ($attributes as $attribute) {
            unset( $taxes['pa_' . $attribute->attribute_name] );
        }
        ?>
        <label for='erase_taxanomy'><p>Таксаномии:</p></label>
        <select name='erase_taxanomy' id='erase_taxanomy'>
            <?php
            $count = 0;
            foreach ($taxes as $name => $text) {
                echo "<option value='{$name}'>" . __($text) . "</option>";
                $count++;
            }
            ?>
        </select>
        <div id='terms_wrapper'></div>
        <?php
        if( wp_is_ajax() ) {
            parent::ajax_answer('Всего терминов', 1, array('count' => $count) );
        }
        return $count;
    }
}
new EraserAdminPage();
