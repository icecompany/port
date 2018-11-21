<?php
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
$noedit = array('jform[task]','jform[contractID]','jform[dat]');
$action = JRoute::_('index.php?option=com_projects&amp;view=todo&amp;layout=edit&amp;id=' . (int)$this->item->id);
$return = JFactory::getApplication()->input->get('return', null);
if ($return != null)
{
    $action .= "&amp;return={$return}";
}
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task == 'todo.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {*/
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    }
</script>
<form action="<?php echo $action; ?>"
      method="post" name="adminForm" id="adminForm" xmlns="http://www.w3.org/1999/html" class="form-validate">
    <div class="row-fluid">
        <div class="span12 form-horizontal">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#general" data-toggle="tab"><?php echo JText::sprintf('COM_PROJECTS_BLANK_TODO');?></a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="general">
                    <fieldset class="adminform">
                        <div class="control-group form-inline">
                            <?php foreach ($this->form->getFieldset('names') as $field) :
                                if ($field->name == 'jform[managerID]' && !$this->isAdmin) continue; ?>
                                <div class="control-label"><?php echo $field->label; ?></div>
                                <div class="controls">
                                    <?php echo ($this->isAdmin || (!$this->isAdmin && !in_array($field->name, $noedit))) ? $field->input : $field->value; ?>
                                </div>
                                <br>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <div>
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>
