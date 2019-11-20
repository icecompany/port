<?php
//var_dump($this->items);
?>

<?php foreach ($this->items['managers'] as $managerID => $manager) :?>
    <tr>
        <td><?php echo $manager;?></td>
        <td><?php echo $this->items['items'][$managerID]['current']['expires'] ?? JText::sprintf('COM_PROJECTS_ERROR_NO_DATA');?></td>
        <td><?php echo $this->items['items'][$managerID]['dynamic']['expires'] ?? JText::sprintf('COM_PROJECTS_ERROR_NO_DATA');?></td>
        <?php foreach ($this->items['dates'] as $dat): ?>
            <td><?php echo $this->items['items'][$managerID]['future'][$dat] ?? 0;?></td>
        <?php endforeach;?>
        <td><?php echo $this->items['items'][$managerID]['future']['future'] ?? 0;?></td>
        <td><?php echo $this->items['items'][$managerID]['future']['dynamic'] ?? 0;?></td>
    </tr>
<?php endforeach;?>
