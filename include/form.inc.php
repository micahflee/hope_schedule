<?php

// display a submit button
function form_submit($value='Submit', $label='', $extra='') {
?>
<li>
    <label for="submit"><?=$label?>&nbsp;</label>
    <input type="submit" value="<?=html_sanitize($value)?>">
    <span class="extra"><?=$extra?></span>
</li>
<?php
}

// display a field
function form_field($type, $field='', $label='', $value='', $error='', $checked=false) {
    global $root;
    if($type == 'checkbox') {
    ?>
        <li>
        <input type="checkbox" style="width:2em;" name="<?=html_sanitize($field)?>" value="<?=html_sanitize($value)?>" <?=$checked?' checked="checked"':''?>>
            <label for="<?=html_sanitize($field)?>"><?=html_sanitize($label)?></label>
        </li>
        <?php
    } else {
        ?>
        <li>
            <label for="<?=html_sanitize($field)?>"><?=html_sanitize($label)?></label>
        <?php
        switch($type) {
            case 'text':
                ?><input type="text" name="<?=html_sanitize($field)?>" value="<?=html_sanitize($value)?>"><?php
                break;
            case 'password':
                ?><input type="password" name="<?=html_sanitize($field)?>"><?php
                break;
            case 'textarea':
                ?><textarea name="<?=html_sanitize($field)?>"><?=html_sanitize($value)?></textarea><?php
                break;
            case 'dropdown':
                ?><select name="<?=html_sanitize($field)?>"><?=$value?></select><?php
                break;
            case 'upload': 
                ?><input type="file" name="<?=html_sanitize($field)?>"><?php
                break;
            case 'custom':
                echo $value;
                break;
        }
        ?>
        <?=error_display($error)?>
        </li>
        <?php
    }
}

?>
