<?php
defined('_JEXEC') or die;
?>
<fieldset class="adminform">
    <div class="control-group form-inline">
        <?php foreach ($this->form->getFieldset('addresses') as $field) : ?>
            <div class="control-label">
                <?php echo $field->label; ?>
            </div>
            <div class="controls">
                <?php echo $field->input;
                if (($field->name == 'jform[site]' || $field->name == 'jform[email]') && $field->value != null) {
                    $url = ($field->name == 'jform[site]') ? $field->value : sprintf('mailto:%s', $field->value);
                    echo JHtml::link($url, JText::sprintf('COM_PROJECTS_ACTION_GO'), array('target' => '_blank'));
                }
                if ($field->name == 'jform[phone_1]' || $field->name == 'jform[phone_2]')
                {
                    $checked = "";
                    if ($field->value !== "")
                    {
                        if (stripos($field->value, "+7") !== false)
                        {
                            $checked = " checked";
                        }
                        else
                        {
                            $checked = "";
                        }
                    }
                    else
                    {
                        $checked = " checked";
                    }
                    echo "<input type='checkbox' onclick='setMask(this.id, \"{$field->id}\")' id='mask_{$field->name}' value='1'{$checked} />";
                    echo "<label for='mask_{$field->name}'>", JText::sprintf('COM_PROJECTS_HEAD_EXP_CONTACT_PHONE_MASK'), "</label>";
                }
                ?>
            </div>
            <br>
        <?php endforeach; ?>
    </div>
</fieldset>
