<form>
    <div class="select-bar">
        <label for='erase_taxanomy'>Таксаномии: </label>
        <select id='erase_taxanomy'><?php echo $options; ?></select>
    </div>

    <p class='action-type'>
        <label><input type="radio" name="erase_type" value="not_erase" checked="checked"> Оставить </label>
        <label><input type="radio" name="erase_type" value="erase"> Стереть </label>
    </p>

    <div id='existing_terms_filter' class="result-area"><?php echo $strTermsTable; ?></div>

    <div class="status-bar">
        <p class="logs">Всего терминов: <span id='terms_count'><?php echo $count; ?></span></p>
        <button id="erase-terms" class="button button-primary erase">Стереть данные</button>
        <span class="spinner"><!-- is-active --></span>
    </div>
    <div class='clear'></div>
</form>