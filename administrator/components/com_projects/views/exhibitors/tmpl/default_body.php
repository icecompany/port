<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$ii = $this->state->get('list.start', 0);
foreach ($this->items as $i => $item) :
    ?>
    <tr class="row0">
        <td class="center">
            <?php echo JHtml::_('grid.id', $i, $item['id']); ?>
        </td>
        <td>
            <?php echo ++$ii; ?>
        </td>
        <td>
            <?php echo $item['title'];?>
        </td>
        <?php
        $projectinactive = $this->state->get('filter.projectinactive');
        if (is_numeric($projectinactive)) :?>
            <td>
                <?php echo $item['contract'];?>
            </td>
        <?php endif;?>
        <?php
        $projectactive = $this->state->get('filter.projectactive');
        if (is_numeric($projectactive)) :?>
            <td>
                <?php echo $item['contracts'];?>
            </td>
        <?php endif;?>
        <td style="<?php echo $item['style'];?>">
            <?php echo $item['manager'];?>
        </td>
        <td>
            <?php echo $item['region'];?>
        </td>
        <td>
            <?php echo $item['id'];?>
        </td>
    </tr>
<?php endforeach; ?>