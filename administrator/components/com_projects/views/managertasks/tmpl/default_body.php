<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$ii = JFactory::getApplication()->input->getInt('limitstart', 0);
$managers = $this->items['managers'];
foreach ($this->items['items'] as $i => $item) :
    ?>
    <tr class="row0">
        <td><?php echo ++$ii; ?></td>
        <td><?php echo $managers[$i]; ?></td>
        <td><?php echo $item['todos_expires']['today'];?></td>
        <td style="border-left: none;"><?php echo $item['todos_expires']['dynamic'];?></td>
        <td><?php echo $item['todos_plan']['today'];?></td>
        <td style="border-left: none;"><?php echo $item['todos_plan']['dynamic'];?></td>
        <td><?php echo $item['todos_future']['today'];?></td>
        <td style="border-left: none;"><?php echo $item['todos_future']['dynamic'];?></td>
        <td><?php echo $item['todos_completed']['today'];?></td>
        <td style="border-left: none;"><?php echo $item['todos_completed']['dynamic'];?></td>
    </tr>
<?php endforeach; ?>
