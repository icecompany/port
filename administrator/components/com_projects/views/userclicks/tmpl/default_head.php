<?php
defined('_JEXEC') or die;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<tr>
    <th style="width: 70%;">
        <?php echo JText::sprintf('COM_PROJECTS_HEAD_MENU_ITEM'); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_PROJECTS_HEAD_MENU_ITEM_CLICKS_COUNT', 'cnt', $listDirn, $listOrder); ?>
    </th>
</tr>