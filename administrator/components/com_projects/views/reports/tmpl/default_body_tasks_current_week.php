<?php
defined('_JEXEC') or die;
$params = array('target' => '_blank');
?>

<?php foreach ($this->items['managers'] as $managerID => $manager) :?>
    <tr>
        <td><?php echo $manager;?></td>
        <?php foreach ($this->items['dates'] as $dat): ?>
            <td style="border-left: 1px dotted grey;"><?php echo $this->items['items'][$managerID][$dat]['todos_expires'];?></td>
            <td><?php echo $this->items['items'][$managerID][$dat]['todos_plan'];?></td>
            <td><?php echo $this->items['items'][$managerID][$dat]['todos_completed'];?></td>
        <?php endforeach;?>
        <td style="border-left: 1px dotted grey;"><?php echo $this->items['total'][$managerID]['todos_plan'];?></td>
        <td><?php echo $this->items['total'][$managerID]['todos_completed'];?></td>
        <td><?php echo $this->items['total'][$managerID]['todos_completed'] - $this->items['total'][$managerID]['todos_plan'];?></td>
    </tr>
<?php endforeach;?>
