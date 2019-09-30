<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$title = JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_SUM_TOTAL');
$diff = (!$this->userSettings['contracts_v2-column_id']) ? 3 : 4;
$last = (!$this->userSettings['contracts_v2-column_id']) ? 1 : 2;
$colspan = $this->columnsCount - $diff;
?>
<tr>
    <td colspan="<?php echo $colspan; ?>" style="text-align: right;">
        <?php echo $title, " ", JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_RUB');?>
    </td>
    <td>
        <?php echo $this->items['total']['amounts']['rub'];?>
    </td>
    <td>
        <?php echo $this->items['total']['payments']['rub'];?>
    </td>
    <td colspan="<?php echo $last;?>">
        <?php echo $this->items['total']['debt']['rub'];?>
    </td>
</tr>
<tr>
    <td colspan="<?php echo $colspan; ?>" style="text-align: right;">
        <?php echo JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_USD');?>
    </td>
    <td>
        <?php echo $this->items['total']['amounts']['usd'];?>
    </td>
    <td>
        <?php echo $this->items['total']['payments']['usd'];?>
    </td>
    <td colspan="<?php echo $last;?>">
        <?php echo $this->items['total']['debt']['usd'];?>
    </td>
</tr>
<tr>
    <td colspan="<?php echo $colspan ?>" style="text-align: right;">
        <?php echo JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_EUR');?>
    </td>
    <td>
        <?php echo $this->items['total']['amounts']['eur'];?>
    </td>
    <td>
        <?php echo $this->items['total']['payments']['eur'];?>
    </td>
    <td colspan="<?php echo $last;?>">
        <?php echo $this->items['total']['debt']['eur'];?>
    </td>
</tr>