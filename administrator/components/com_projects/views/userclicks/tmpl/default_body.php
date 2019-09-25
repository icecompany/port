<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$ii = JFactory::getApplication()->input->getInt('limitstart', 0);
foreach ($this->items['items'] as $i => $item) :
    ?>
    <tr class="row0">
        <td>
            <?php echo $item['section']; ?>
        </td>
        <td>
            <?php echo $item['cnt']; ?>
        </td>
    </tr>
<?php endforeach; ?>
