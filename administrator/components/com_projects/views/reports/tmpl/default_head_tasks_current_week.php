<?php
defined('_JEXEC') or die;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

?>
<tr>
    <th rowspan="2">
        <?php echo JText::sprintf('COM_PROJECTS_HEAD_TMPL_MANAGER'); ?>
    </th>
    <?php foreach ($this->items['dates'] as $dat): ?>
        <th colspan="3" style="text-align: center;">
            <?php echo JDate::getInstance($dat)->format("d.m (D)"); ?>
        </th>
    <?php endforeach; ?>
    <th colspan="3" style="text-align: center;">
        <?php echo JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_SUM'); ?>
    </th>
</tr>
<tr>
    <?php foreach ($this->items['dates'] as $dat): ?>
        <th style="border-left: 1px dotted grey; font-size: 0.8em;"><?php echo JText::sprintf('COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_EXPIRES'); ?></th>
        <th style="font-size: 0.8em;"><?php echo JText::sprintf('COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_PLAN'); ?></th>
        <th style="font-size: 0.8em;"><?php echo JText::sprintf('COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_COMPLETED'); ?></th>
    <?php endforeach; ?>
    <th style="border-left: 1px dotted grey; font-size: 0.8em;"><?php echo JText::sprintf('COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_PLAN'); ?></th>
    <th style="font-size: 0.8em;"><?php echo JText::sprintf('COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_COMPLETED'); ?></th>
    <th style="font-size: 0.8em;"><?php echo JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DIFF'); ?></th>
</tr>
