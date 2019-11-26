<?php
defined('_JEXEC') or die;
$params = array('target' => '_blank');
?>

<?php foreach ($this->items['managers'] as $managerID => $manager) :?>
    <tr>
        <td><?php echo $manager;?></td>
        <td>
            <?php
            $url = JRoute::_("index.php?option=com_projects&amp;view=todos&amp;uid={$managerID}&amp;expire=1");

            if ($this->items['items'][$managerID]['current']['expires'] != null) {
                echo JHtml::link($url, $this->items['items'][$managerID]['current']['expires'], $params);
            }
            else {
                echo $this->items['items'][$managerID]['current']['expires'] ?? JText::sprintf('COM_PROJECTS_ERROR_NO_DATA');
            }
            ?>
        </td>
        <td>
            <?php
            $color = ($this->items['items'][$managerID]['dynamic']['expires'] <= 0) ? 'green' : 'red';
            ?>
            <span style="color: <?php echo $color;?>"><?php echo $this->items['items'][$managerID]['dynamic']['expires'] ?? JText::sprintf('COM_PROJECTS_ERROR_NO_DATA');?></span>
        </td>
        <?php foreach ($this->items['dates'] as $dat): ?>
            <td>
                <?php
                $url = JRoute::_("index.php?option=com_projects&amp;view=todos&amp;uid={$managerID}&amp;date={$dat}");
                if ($this->items['items'][$managerID]['future'][$dat] == 0 || $this->items['items'][$managerID]['future'][$dat] == null) {
                    echo 0;
                }
                else {
                    echo JHtml::link($url, $this->items['items'][$managerID]['future'][$dat] ?? 0, $params);
                }
                ?>
            </td>
        <?php endforeach;?>
        <td>
            <?php
            $url = JRoute::_("index.php?option=com_projects&amp;view=todos&amp;uid={$managerID}&amp;date={$this->dat}&futures=1");
            if ($this->items['items'][$managerID]['current']['after_next_week'] == 0 || $this->items['items'][$managerID]['current']['after_next_week'] == null) {
                echo 0;
            }
            else {
                echo JHtml::link($url, $this->items['items'][$managerID]['current']['after_next_week'] ?? 0, $params);
            }
            ?>
        </td>
        <td><?php echo $this->items['items'][$managerID]['dynamic']['after_next_week'] ?? 0;?></td>
        <td><?php echo $this->items['items'][$managerID]['future']['week'] ?? 0;?></td>
        <td>
            <?php echo $this->items['items'][$managerID]['on_period']['expires'] ?? JText::sprintf('COM_PROJECTS_ERROR_NO_DATA');?>
        </td>
    </tr>
<?php endforeach;?>
