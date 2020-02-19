<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$ii = JFactory::getApplication()->input->getInt('limitstart', 0);
foreach ($this->items['items'] as $i => $item) :
    ?>
    <tr class="row0">
        <td class="center">
            <?php echo JHtml::_('grid.id', $i, $item['id']); ?>
        </td>
        <td>
            <?php echo ++$ii; ?>
        </td>
        <td>
            <?php echo $item['num']; ?>
        </td>
        <td>
            <?php echo $item['dat']; ?>
        </td>
        <td>
            <?php echo $item['stands']; ?>
        </td>
        <td>
            <?php echo $item['edit']; ?>
        </td>
        <td>
            <?php echo $item['project']; ?>
        </td>
        <td>
            <?php echo $item['exhibitor']; ?>
        </td>
        <td>
            <?php echo $item['todos']; ?>
        </td>
        <?php if ($this->userSettings['contracts_v2-column_manager']): ?>
            <td>
                <?php echo $item['manager']; ?>
            </td>
        <?php endif; ?>
        <td>
            <?php echo $item['status']; ?>
        </td>
        <?php if ($this->userSettings['contracts_v2-column_parent']): ?>
            <td>
                <?php echo $item['isCoExp']; ?>
            </td>
        <?php endif; ?>
        <?php if ($this->userSettings['contracts_v2-column_doc_status']): ?>
            <td>
                <?php echo $item['doc_status']; ?>
            </td>
        <?php endif; ?>
        <td>
            <?php echo $item['amount']; ?>
        </td>
        <td>
            <?php echo $item['payments']; ?>
        </td>
        <td style="color: <?php echo $item['color'];?>">
            <?php echo $item['debt']; ?>
        </td>
        <?php if ($this->userSettings['contracts_v2-column_id']): ?>
            <td>
                <?php echo $item['id']; ?>
            </td>
        <?php endif; ?>
    </tr>
<?php endforeach; ?>
