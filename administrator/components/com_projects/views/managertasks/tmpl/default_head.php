<?php
defined('_JEXEC') or die;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<tr>
    <th rowspan="2">№</th>
    <th rowspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_MANAGER_STAT_MANAGER"); ?></th>
    <th colspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_EXPIRES"); ?></th>
    <th colspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_PLAN"); ?></th>
    <th colspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_FUTURE"); ?></th>
    <th colspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_COMPLETED"); ?></th>
</tr>
<tr>
    <?php for($i = 0; $i < 4; $i++): ?>
        <th><?php echo $this->dat;?></th>
        <th style="border-left: none; font-size: 0.7em;">Динамика</th>
    <?php endfor;?>
</tr>