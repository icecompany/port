<?php
defined('_JEXEC') or die;
$listOrder    = $this->escape($this->state->get('list.ordering'));
$listDirn    = $this->escape($this->state->get('list.direction'));
?>
<tr>
    <th style="width:1%" class="hidden-phone">
        <?php echo JHtml::_('grid.checkall'); ?>
    </th>
    <th style="width:1%">
        №
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_PROJECTS_HEAD_TITLE', 's.title', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('searchtools.sort', 'COM_PROJECTS_HEAD_TITLE_PRICE_DESC', 'price', $listDirn, $listOrder); ?>
    </th>
    <th style="width:1%">
        <?php echo JHtml::_('searchtools.sort', 'ID', 's.id', $listDirn, $listOrder); ?>
    </th>
</tr>