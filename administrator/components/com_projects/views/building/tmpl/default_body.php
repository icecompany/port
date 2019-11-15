<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$ii = JFactory::getApplication()->input->getInt('limitstart', 0);
$colspan = (ProjectsHelper::canDo('core.general')) ? 11 + count($this->advanced_items) : 10 + count($this->advanced_items);
$last_pavilion = '';
foreach ($this->items['stands'] as $item) :?>
    <tr class="row0<?php if ($item['expired']) echo ' expired'; ?>">
        <td>
            <?php echo ++$ii; ?>
        </td>
        <td>
            <?php echo $item['stand']; ?>
        </td>
        <td>
            <?php echo $item['sq']; ?>
        </td>
        <td>
            <?php echo $item['status']; ?>
        </td>
        <td>
            <?php echo $item['exhibitor']; ?>
        </td>
        <?php if ($this->userSettings['building-show_city']): ?>
            <td>
                <?php echo $item['city']; ?>
            </td>
        <?php endif; ?>
        <?php if ($this->userSettings['building-show_city_fact']): ?>
            <td>
                <?php echo $item['city_fact']; ?>
            </td>
        <?php endif; ?>
        <td>
            <?php echo $item['manager']; ?>
        </td>
        <td>
            <?php echo $item['exp_status']; ?>
        </td>
        <td>
            <?php echo $item['tip']; ?>
        </td>
        <?php if (ProjectsHelper::canDo('core.general')) : ?>
            <td>
                <?php echo $item['scheme']; ?>
            </td>
        <?php endif; ?>
        <td>
            <?php echo $item['contract']; ?>
        </td>
        <td>
            <?php echo $item['freeze']; ?>
        </td>
        <?php foreach ($this->advanced_items as $advanced_item) :?>
            <td>
                <?php echo $this->items['advanced'][$advanced_item][$item['standID']] ?? 0;?>
            </td>
        <?php endforeach;?>
    </tr>
<?php endforeach; ?>
