<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$title = JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_SUM');
?>
<tr>
    <td colspan="2"><?php echo $title;?></td>
    <td><?php echo $this->items['total']['todos_expires']['today'];?></td>
    <td style="border-left: none;"><?php echo $this->items['total']['todos_expires']['dynamic'];?></td>
    <td><?php echo $this->items['total']['todos_plan']['today'];?></td>
    <td style="border-left: none;"><?php echo $this->items['total']['todos_plan']['dynamic'];?></td>
    <td><?php echo $this->items['total']['todos_future']['today'];?></td>
    <td style="border-left: none;"><?php echo $this->items['total']['todos_future']['dynamic'];?></td>
    <td><?php echo $this->items['total']['todos_completed']['today'];?></td>
    <td style="border-left: none;"><?php echo $this->items['total']['todos_completed']['dynamic'];?></td>
</tr>
