<?php
defined('_JEXEC') or die;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

?>
<tr>
    <th rowspan="2">
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_TMPL_MANAGER', 'manager', $listDirn, $listOrder); ?>
    </th>
    <th colspan="2" class="center">
        <?php echo JText::sprintf('COM_PROJECTS_HEAD_TODO_STATE_EXPIRES');?>
    </th>
    <?php foreach ($this->items['dates'] as $dat): ?>
        <th rowspan="2" style="vertical-align: bottom;">
            <?php echo JDate::getInstance($dat)->format("d.m");?>
        </th>
    <?php endforeach;?>
    <th colspan="2" class="center">

    </th>
</tr>
<tr>
    <th><?php echo JDate::getInstance($this->dat)->format("d.m");?></th>
    <th style="border-left: none; font-size: 0.7em;">Динамика</th>
    <th><?php echo JText::sprintf('COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_FUTURE');?></th>
    <th style="border-left: none; font-size: 0.7em;">Динамика</th>
</tr>
