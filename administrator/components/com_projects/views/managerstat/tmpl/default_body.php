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
        <td><?php echo $item['status_2']['today'];?></td>
        <td><?php echo $item['status_2']['dynamic'];?></td>
        <td><?php echo $item['status_3']['today'];?></td>
        <td><?php echo $item['status_3']['dynamic'];?></td>
        <td><?php echo $item['status_4']['today'];?></td>
        <td><?php echo $item['status_4']['dynamic'];?></td>
        <td><?php echo $item['status_1']['today'];?></td>
        <td><?php echo $item['status_1']['dynamic'];?></td>
        <td><?php echo $item['status_9']['today'];?></td>
        <td><?php echo $item['status_9']['dynamic'];?></td>
        <td><?php echo $item['status_0']['today'];?></td>
        <td><?php echo $item['status_0']['dynamic'];?></td>
        <td><?php echo $item['exhibitors']['today'];?></td>
        <td><?php echo $item['exhibitors']['dynamic'];?></td>
        <td><?php echo $item['cwt'];?></td>
    </tr>
<?php endforeach; ?>
