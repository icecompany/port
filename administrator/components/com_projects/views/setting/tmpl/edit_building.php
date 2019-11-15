<?php
defined('_JEXEC') or die; ?>
<div class="row-fluid">
    <div class="span6">
        <fieldset class="adminform">
            <div class="control-group form-inline">
                <?php foreach ($this->form->getFieldset('building') as $field) : ?>
                    <?php echo $field->renderField(); ?>
                <?php endforeach; ?>
            </div>
        </fieldset>
    </div>
</div>
