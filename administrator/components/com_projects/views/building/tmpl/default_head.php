<?php
defined('_JEXEC') or die;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$indexes = array();
$j = 0;
?>
<tr>
    <th width="1%">
        №
    </th>
    <th width="5%">
        <?php echo JHtml::_('grid.sort', '№', 'stand', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_ITEM_HEAD_SQUARE', 'sq', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_CONTRACT_STAND_STATUS', 's.status', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_TODO_EXP', 'title_ru_short', $listDirn, $listOrder); ?>
    </th>
    <?php if ($this->userSettings['building-show_city']): ?>
        <th>
            <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_CITY', 'reg', $listDirn, $listOrder); ?>
        </th>
    <?php endif; ?>
    <?php if ($this->userSettings['building-show_city_fact']): ?>
        <th>
            <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_CITY_FACT', 'reg_fact', $listDirn, $listOrder); ?>
        </th>
    <?php endif; ?>
    <?php if ($this->userSettings['building-show_addr']): ?>
        <th>
            <?php echo JText::sprintf('COM_PROJECTS_HEAD_ADDR'); ?>
        </th>
    <?php endif; ?>
    <?php if ($this->userSettings['building-show_addr_fact']): ?>
        <th>
            <?php echo JText::sprintf('COM_PROJECTS_HEAD_ADDR_FACT'); ?>
        </th>
    <?php endif; ?>
    <th>
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_CONTRACT_MANAGER', 'manager', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_CONTRACT_STATUS_DOG', 'exp_status', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_CONTRACT_STAND_TYPE', 's.tip', $listDirn, $listOrder); ?>
    </th>
    <?php if (ProjectsHelper::canDo('core.general')) :?>
    <th>
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_CONTRACT_STAND_SCHEME', 'scheme', $listDirn, $listOrder); ?>
    </th>
    <?php endif;?>
    <th>
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_PAYMENT_CONTRACT_DESC', 'contract', $listDirn, $listOrder); ?>
    </th>
    <th>
        <?php echo JHtml::_('grid.sort', 'COM_PROJECTS_HEAD_CONTRACT_STAND_FREEZE', 'freeze', $listDirn, $listOrder); ?>
    </th>
    <?php foreach ($this->advanced_items as $advanced_item) :?>
        <th width="50px">
            <?php
            echo $advanced_item;
            $indexes[$advanced_item] = $j;
            $j++;
            ?>
        </th>
    <?php endforeach;?>
</tr>