<?php
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('script', $this->script);
HTMLHelper::_('script', 'com_projects/jquery.maskedinput.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_projects/style.css', array('version' => 'auto', 'relative' => true));
$action = JRoute::_('index.php?option=com_projects&amp;view=todo&amp;layout=notify&amp;id=' . (int)$this->item->id);
$return = JFactory::getApplication()->input->get('return', null);
if ($return != null) {
    $action .= "&amp;return={$return}";
}
?>
<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task === 'todo.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {*/
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    }
</script>
<form action="<?php echo $action; ?>"
      method="post" name="adminForm" id="adminForm" xmlns="http://www.w3.org/1999/html" class="form-validate">
    <div class="row-fluid">
        <div class="span12 form-horizontal">
            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
            <div class="tab-content">
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::sprintf('COM_PROJECTS_BLANK_NOTIFY')); ?>
                <div class="row-fluid">
                    <div class="span6">
                        <?php echo $this->loadTemplate('text'); ?>
                    </div>
                    <div class="span6">

                    </div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
            </div>
            <?php echo JHtml::_('bootstrap.endTabSet'); ?>
        </div>
        <div>
            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>

