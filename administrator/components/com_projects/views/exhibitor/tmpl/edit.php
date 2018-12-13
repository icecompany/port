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
$action = JRoute::_('index.php?option=com_projects&amp;view=exhibitor&amp;layout=edit&amp;id=' . (int)$this->item->id);
$return = JFactory::getApplication()->input->get('return', null);
if ($return != null)
{
    $action .= "&amp;return={$return}";
}
?>
<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task === 'exhibitor.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {*/
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
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::sprintf('COM_PROJECTS_BLANK_EXHIBITOR')); ?>
                <div class="row-fluid">
                    <div class="span4">
                        <div>
                            <?php echo $this->loadTemplate('general'); ?>
                        </div>
                        <div>
                            <?php echo $this->loadTemplate('similar'); ?>
                        </div>
                    </div>
                    <div class="span4">
                        <?php echo $this->loadTemplate('addresses'); ?>
                    </div>
                    <div class="span4">
                        <?php echo $this->loadTemplate('bank'); ?>
                    </div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'contact', JText::sprintf('COM_PROJECTS_BLANK_EXHIBITOR_CONTACTS')); ?>
                <div class="row-fluid">
                    <div class="span12">
                        <?php echo $this->loadTemplate('contacts'); ?>
                    </div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'activities', JText::sprintf('COM_PROJECTS_BLANK_EXHIBITOR_ACTIVITIES')); ?>
                <div class="row-fluid">
                    <div class="span6">
                        <?php echo $this->loadTemplate('acts'); ?>
                    </div>
                    <div class="span6">

                    </div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php if ($this->item->id != 0): ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'history', JText::sprintf('COM_PROJECTS_BLANK_EXHIBITOR_HISTORY')); ?>
                    <div class="row-fluid">
                        <div class="span6">
                            <?php echo $this->loadTemplate('history_active'); ?>
                        </div>
                        <div class="span6">
                            <?php echo $this->loadTemplate('history_complete'); ?>
                        </div>
                    </div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif; ?>
            </div>
            <?php echo JHtml::_('bootstrap.endTabSet'); ?>
        </div>
        <div>
            <input type="hidden" name="task" value=""/>
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>

