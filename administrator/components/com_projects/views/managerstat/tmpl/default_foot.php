<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
?>
<tr>
    <td colspan="<?php echo $this->columnsCount;?>" class="pagination-centered"><?php echo $this->pagination->getListFooter(); ?></td>
</tr>
<tr>
    <td colspan="<?php echo $this->columnsCount;?>" style="font-style: italic;">
        <?php echo JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_IS_DELEGATED_HINT');?>
    </td>
</tr>