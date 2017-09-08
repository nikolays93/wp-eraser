<form>
    <div class="select-bar">
        <label for="erase_post_type">Типы записей: </label>
        <select id="erase_post_type"><?php echo $options; ?></select>
    </div>

    <p class="action-type">
        <label><input type="radio" name="erase_type" value="not_erase" checked="checked"> Оставить </label>
        <label><input type="radio" name="erase_type" value="erase"> Стереть </label>
    </p>
    <div id="existing_posts_filter" class="result-area"><?php echo $strPostsTable; ?></div>

<!--     <div class="result-input">
        <label>
            Или введите ID через запятую.
            <input type="text" class="widefat">
        </label>
    </div> -->

    <div class="status-bar">
        <p class="logs">Всего записей: <span id="posts_count"><?php echo $count; ?></span></p>
        <button id="erase-posts" class="button button-primary erase">Стереть данные</button>
        <span class="spinner"><!-- is-active --></span>
    </div>
    <div class="clear"></div>
</form>