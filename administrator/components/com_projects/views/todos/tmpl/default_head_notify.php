<?php
defined('_JEXEC') or die;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<tr>
    <th width="1%" class="hidden-phone">
        <input type="checkbox" name="checkall-toggle" value=""
               title="<?php echo JText::sprintf('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
    </th>
    <th width="1%">
        №
    </th>
    <th width="5%">
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_TODO_DATE_OPEN', 't.dat_open', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_PROJECTS_BLANK_NOTIFY'); ?>
    </th>
    <th width="8%">
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_TODO_CONTRACT', 'c.number', $listDirn, $listOrder); ?>
    </th>
    <th width="8%">
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_TODO_PROJECT', 'project', $listDirn, $listOrder); ?>
    </th>
    <th width="15%">
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_TODO_EXP', 'e.title_ru_short', $listDirn, $listOrder); ?>
    </th>
</tr>