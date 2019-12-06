<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$total = array('current' => 0, 'completed' => 0);
?>
<tr>
    <td style="font-weight: bold; text-align: right;"><?php echo JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_SUM');?></td>
    <?php foreach ($this->items['dates'] as $dat): ?>
        <td style="border-left: 1px dotted grey;"><?php echo $this->items['total'][$dat]['todos_expires'];?></td>
        <td><?php
            echo $this->items['total'][$dat]['todos_plan'];
            $total['current'] += $this->items['total'][$dat]['todos_plan'];
            $total['completed'] += $this->items['total'][$dat]['todos_completed'];
            ?>
        </td>
        <td><?php echo $this->items['total'][$dat]['todos_completed'];?></td>
    <?php endforeach;?>
    <td style="border-left: 1px dotted grey;"><?php echo $total['current'];?></td>
    <td><?php echo $total['completed'];?></td>
    <td><?php echo $total['completed'] - $total['current'];?></td>
</tr>
