<?php
defined('_JEXEC') or die;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<tr>
    <th rowspan="2">№</th>
    <th rowspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_MANAGER_STAT_MANAGER"); ?></th>
    <th colspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_STATUS_2_SHORT"); ?></th>
    <th colspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_STATUS_3_SHORT"); ?></th>
    <th colspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_STATUS_4_SHORT"); ?></th>
    <th colspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_STATUS_1_SHORT"); ?></th>
    <th colspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_STATUS_9_SHORT"); ?></th>
    <th colspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_STATUS_0_SHORT"); ?></th>
    <th rowspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_MANAGER_STAT_COMPANIES"); ?></th>
    <th rowspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC"); ?></th>
    <th rowspan="2"><?php echo JText::sprintf("COM_PROJECTS_HEAD_CONTRACTS_WITHOUT_TODOS"); ?></th>
</tr>
<tr>
    <?php for($i = 0; $i < 6; $i++): ?>
        <th><?php echo $this->dat;?></th>
        <th style="border-left: none;"><span class="icon-chart"></span></th>
    <?php endfor;?>
</tr>