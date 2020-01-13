<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$columns = count($this->items) + 12;
?>
<tr>
    <td colspan="<?php echo $columns;?>" class="pagination-centered"><?php echo $this->pagination->getListFooter(); ?></td>
</tr>
<tr>
    <td colspan="<?php echo $columns;?>" style="font-style: italic;">
        <?php echo JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_IS_DELEGATED_HINT');?>
    </td>
</tr>