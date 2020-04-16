<?php
defined('_JEXEC') or die;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<tr>
    <th width="1%" class="hidden-phone">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th width="1%">
        №
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_PROJECTS_HEAD_TODO_DATE_OPEN', 't.dat_open', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_PROJECTS_HEAD_TODO_DATE', 't.dat', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_PROJECTS_HEAD_TODO_CONTRACT', 't.number', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_PROJECTS_HEAD_TODO_PROJECT', 'project', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_PROJECTS_HEAD_TODO_EXP', 'e.title_ru_short', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_PROJECTS_HEAD_TODO_TASK'); ?>
    </th>
    <th>
        <?php echo JText::sprintf('COM_PROJECTS_HEAD_TODO_RESULT'); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_PROJECTS_HEAD_TODO_OPEN', 'open', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_PROJECTS_HEAD_TODO_DATE_CLOSE', 't.dat_close', $listDirn, $listOrder); ?>
    </th>
    <?php if ($this->isAdmin): ?>
        <th>
            <?php echo JHtml::_('searchtools.sort', 'COM_PROJECTS_HEAD_TODO_MANAGER', 'manager', $listDirn, $listOrder); ?>
        </th>
    <?php endif; ?>
</tr>