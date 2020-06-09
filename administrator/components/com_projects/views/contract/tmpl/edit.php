<?php
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
use Joomla\CMS\HTML\HTMLHelper;
HTMLHelper::_('script', $this->script, array('version' => 'auto'));
HTMLHelper::_('stylesheet', 'com_projects/style.css', array('version' => 'auto', 'relative' => true));
$action = JRoute::_('index.php?option=com_projects&amp;view=contract&amp;layout=edit&amp;id=' . (int)$this->item->id);
$return = JFactory::getApplication()->input->get('return', null);

if ($return != null)
{
    $action .= "&amp;return={$return}";
}
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task === 'contract.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {*/
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    };
</script>

<form action="<?php echo $action; ?>"
      method="post" name="adminForm" id="adminForm" xmlns="http://www.w3.org/1999/html" class="form-validate" enctype="multipart/form-data">
    <div class="row-fluid">
        <div class="span12 form-horizontal">
            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general', 'useCookie' => true)); ?>
            <div class="tab-content">
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::sprintf('COM_PROJECTS_BLANK_CONTRACT')); ?>
                <div class="row-fluid">
                    <div class="span6">
                        <?php echo $this->loadTemplate('general');?>
                    </div>
                    <div class="span6">
                        <?php if ($this->item->id != null): ?>
                            <?php if (!empty($this->item->coExps)) echo $this->loadTemplate('coexps');?>
                            <?php echo $this->loadTemplate('links');?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php if ($this->item->id != null): ?>
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'priceAdd', JText::sprintf('COM_PROJECTS_BLANK_CONTRACT_PRICE_ADD')); ?>
                <div class="row-fluid">
                    <div class="span12">
                        <?php echo $this->loadTemplate('price');?>
                    </div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif;?>
                <?php if ($this->item->id != null): ?>
                <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'priceCurrent', JText::sprintf('COM_PROJECTS_BLANK_CONTRACT_PRICE_CURRENT')); ?>
                <div class="row-fluid">
                    <div class="span12">
                        <?php echo $this->loadTemplate('summary');?>
                    </div>
                </div>
                <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif;?>
                <?php if ($this->item->id != null): ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'todos', JText::sprintf('COM_PROJECTS_BLANK_TODOS')); ?>
                    <div class="row-fluid">
                        <div>
                            <?php echo $this->loadTemplate('todos');?>
                        </div>
                    </div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif;?>
                <?php if ($this->item->id != null): ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'files', JText::sprintf('COM_PROJECTS_BLANK_FILES')); ?>
                    <div class="row-fluid">
                        <div class="span6">
                            <?php echo $this->loadTemplate('files_list');?>
                        </div>
                        <div class="span6">
                            <?php echo $this->loadTemplate('files_preview');?>
                        </div>
                    </div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif;?>
                <?php if ($this->item->id != null): ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'stands', JText::sprintf(($this->tip == 0) ? 'COM_PROJECTS_BLANK_STANDS' : 'COM_PROJECTS_BLANK_ROOMS')); ?>
                    <div class="row-fluid">
                        <div>
                            <?php echo $this->loadTemplate(($this->tip == 0) ? 'stands' : 'rooms');?>
                        </div>
                    </div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif;?>
                <?php if ($this->item->finanses != null): ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'finanses', JText::sprintf('COM_PROJECTS_BLANK_FINANSES')); ?>
                    <div class="row-fluid">
                        <div>
                            <?php echo $this->loadTemplate('finanses');?>
                        </div>
                    </div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif;?>
                <?php if ($this->item->id != null): ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'catalog', JText::sprintf('COM_PROJECTS_BLANK_CATALOG_SENT')); ?>
                    <div class="row-fluid">
                        <div>
                            <?php echo $this->loadTemplate('catalog');?>
                        </div>
                    </div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif;?>
                <?php if ($this->item->id != null && ProjectsHelper::canDo('core.admin')): ?>
                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'settings', JText::sprintf('COM_PROJECTS_BLANK_CONTRACT_SETTINGS')); ?>
                    <div class="row-fluid">
                        <div class="span12">
                            <?php echo $this->loadTemplate('settings');?>
                        </div>
                    </div>
                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                <?php endif;?>
            </div>
            <?php echo JHtml::_('bootstrap.endTabSet'); ?>
        </div>
        <div>
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>
<?php echo $this->loadTemplate('exhibitor');?>
