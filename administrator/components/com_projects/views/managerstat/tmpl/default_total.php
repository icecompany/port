<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$title = JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_SUM');
?>
<tr>
    <td colspan="2"><?php echo $title;?></td>
    <td><?php echo $this->items['total']['status_-1']['today'];?></td>
    <td style="border-left: none;"><?php echo $this->items['total']['status_-1']['dynamic'];?></td>
    <td><?php echo $this->items['total']['status_2']['today'];?></td>
    <td style="border-left: none;"><?php echo $this->items['total']['status_2']['dynamic'];?></td>
    <td><?php echo $this->items['total']['status_3']['today'];?></td>
    <td style="border-left: none;"><?php echo $this->items['total']['status_3']['dynamic'];?></td>
    <td><?php echo $this->items['total']['status_4']['today'];?></td>
    <td style="border-left: none;"><?php echo $this->items['total']['status_4']['dynamic'];?></td>
    <td><?php echo $this->items['total']['status_1']['today'];?></td>
    <td style="border-left: none;"><?php echo $this->items['total']['status_1']['dynamic'];?></td>
    <td><?php echo $this->items['total']['status_9']['today'];?></td>
    <td style="border-left: none;"><?php echo $this->items['total']['status_9']['dynamic'];?></td>
    <td><?php echo $this->items['total']['status_0']['today'];?></td>
    <td style="border-left: none;"><?php echo $this->items['total']['status_0']['dynamic'];?></td>
    <td><?php echo $this->items['total']['exhibitors']['today'];?></td>
    <td><?php echo $this->items['total']['exhibitors']['dynamic'];?></td>
    <td><?php echo $this->items['total']['cwt'];?></td>
</tr>
