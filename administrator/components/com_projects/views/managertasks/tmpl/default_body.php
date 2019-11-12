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
        <td><?php echo $item['todos_expires']['today'] ?? '0';?></td>
        <td style="border-left: none;"><?php echo $item['todos_expires']['dynamic'] ?? '0';?></td>
        <td><?php echo $item['todos_plan']['today'] ?? '0';?></td>
        <td style="border-left: none;"><?php echo $item['todos_plan']['dynamic'] ?? '0';?></td>
        <td><?php echo $item['todos_future']['today'] ?? '0';?></td>
        <td style="border-left: none;"><?php echo $item['todos_future']['dynamic'] ?? '0';?></td>
        <td><?php echo $item['todos_completed']['today'] ?? '0';?></td>
        <td style="border-left: none;"><?php echo $item['todos_completed']['dynamic'] ?? '0';?></td>
    </tr>
<?php endforeach; ?>
