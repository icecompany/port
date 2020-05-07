<?php
defined('_JEXEC') or die; ?>
<div class="row-fluid">
    <div class="span4">
        <fieldset class="adminform">
            <div class="control-group form-inline">
                <?php foreach ($this->form->getFieldset('contracts_v2') as $field) : ?>
                    <?php echo $field->renderField(); ?>
                <?php endforeach; ?>
            </div>
        </fieldset>
    </div>
    <div class="span4">
        <fieldset class="adminform">
            <div class="control-group form-inline">
                <?php foreach ($this->form->getFieldset('contracts_v2_filters') as $field) : ?>
                    <?php echo $field->renderField(); ?>
                <?php endforeach; ?>
            </div>
        </fieldset>
    </div>
    <div class="span4">
        <fieldset class="adminform">
            <div class="control-group form-inline">
                <?php foreach ($this->form->getFieldset('contracts_v2_columns') as $field) : ?>
                    <?php echo $field->renderField(); ?>
                <?php endforeach; ?>
            </div>
        </fieldset>
    </div>
</div>
