<?php
defined('_JEXEC') or die;
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('searchtools.form');

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('stylesheet', 'com_projects/style.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_projects/no-wrap.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'https://cdn.datatables.net/fixedcolumns/3.2.6/css/fixedColumns.dataTables.min.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_projects/jquery.dataTables.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_projects/dataTables.fixedColumns.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_projects/tdata.js', array('version' => 'auto', 'relative' => true));
$return = base64_encode("index.php?option=com_projects&view=building");
?>
<div class="row-fluid">
    <div id="j-sidebar-container" class="span2">
        <form action="<?php echo JRoute::_("index.php?return={$return}"); ?>" method="post">
            <?php echo $this->sidebar; ?>
        </form>
    </div>
    <div id="j-main-container" class="span10 j-toggle-main">
        <form action="<?php echo JRoute::_('index.php?option=com_projects&amp;view=building'); ?>" method="post"
              name="adminForm" id="adminForm">
            <?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
            <table class="stripe row-border order-column" id="bigtable">
                <thead><?php echo $this->loadTemplate('head'); ?></thead>
                <tbody><?php echo $this->loadTemplate('body'); ?></tbody>
            </table>
            <div>
                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <input type="hidden" name="filter_order"
                       value="<?php echo $this->escape($this->state->get('list.ordering')); ?>"/>
                <input type="hidden" name="filter_order_Dir"
                       value="<?php echo $this->escape($this->state->get('list.direction')); ?>"/>
                <?php echo JHtml::_('form.token'); ?>
            </div>
        </form>
    </div>
</div>