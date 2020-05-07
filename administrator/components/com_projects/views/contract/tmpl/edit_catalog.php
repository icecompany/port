<?php
defined('_JEXEC') or die;
?>
<div>
    <fieldset class="adminform">
        <div class="control-group form-inline">
            <?php foreach ($this->form->getFieldset('catalog') as $field):
                echo $field->renderField();
            endforeach; ?>
        </div>
    </fieldset>
</div>
