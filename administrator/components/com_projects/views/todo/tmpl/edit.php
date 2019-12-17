<?php

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
HTMLHelper::_('script', $this->script);
HTMLHelper::_('stylesheet', 'com_projects/style.css', array('version' => 'auto', 'relative' => true));
$action = JRoute::_('index.php?option=com_projects&amp;view=todo&amp;layout=edit&amp;id=' . (int)$this->item->id);
$return = JFactory::getApplication()->input->get('return', null);
if ($return != null) {
    $action .= "&amp;return={$return}";
}
?>
<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task === 'todo.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    }
</script>
<form action="<?php echo $action; ?>"
      method="post" name="adminForm" id="adminForm" xmlns="http://www.w3.org/1999/html" class="form-validate">
    <div class="row-fluid">
        <div class="span12 form-horizontal">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#general"
                                      data-toggle="tab"><?php echo JText::sprintf('COM_PROJECTS_BLANK_TODO'); ?></a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="general">
                    <div class="row-fluid">
                        <div class="span12">
                            <fieldset class="adminform">
                                <div class="control-group form-inline">
                                    <?php foreach ($this->form->getFieldset('names') as $field) : ?>
                                        <div class="control-label"><?php echo $field->label; ?></div>
                                        <div class="controls">
                                            <?php if ($field->name == 'jform[dat]'): ?>
                                                <div id="hidden-Todos">
                                                    <?php echo JText::sprintf('COM_PROJECTS_HEAD_TODO_ACTIVE_TODOS'); ?>
                                                    :&nbsp;
                                                    <span id="actTodos"></span> - <a id="goTodo" href="#"
                                                                                     target="_blank"><?php echo JText::sprintf('COM_PROJECTS_ACTION_GOTO_TODOS'); ?></a>
                                                </div>
                                            <?php endif; ?>
                                            <?php echo $field->input; ?>
                                        </div>
                                        <br>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>

