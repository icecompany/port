<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$colspan = ($this->isAdmin) ? '16' : '11';
?>
<tr>
    <td colspan="<?php echo $colspan;?>" class="pagination-centered"><?php echo $this->pagination->getListFooter(); ?></td>
</tr>