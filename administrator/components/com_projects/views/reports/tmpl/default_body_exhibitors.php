<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$ii = JFactory::getApplication()->input->getInt('limitstart', 0);
foreach ($this->items as $item) :
    ?>
    <tr>
        <td class="small">
            <?php echo ++$ii; ?>
        </td>
        <td class="small">
            <?php echo $item['exhibitor']; ?>
        </td>
        <?php if (in_array('status', $this->fields)): ?>
            <td class="small">
                <?php echo $item['status']; ?>
            </td>
            <td class="small">
                <?php echo $item['co_exhibitor']; ?>
            </td>
            <td class="small">
                <?php echo $item['number']; ?>
            </td>
            <td class="small">
                <?php echo $item['dat']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('amount', $this->fields)): ?>
            <td class="small">
                <?php echo $item['amount']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('stands', $this->fields)): ?>
            <td class="small">
                <?php echo $item['stands']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('manager', $this->fields)): ?>
            <td class="small">
                <?php echo $item['manager']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('director_name', $this->fields)): ?>
            <td class="small">
                <?php echo $item['director_name']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('director_post', $this->fields)): ?>
            <td class="small">
                <?php echo $item['director_post']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('address_legal', $this->fields)): ?>
            <td class="small">
                <?php echo $item['address_legal']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('address_fact', $this->fields)): ?>
            <td class="small">
                <?php echo $item['address_fact']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('phone', $this->fields)): ?>
            <td class="small">
                <?php echo $item['phones']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('contacts', $this->fields)): ?>
            <td class="small">
                <?php echo $item['sites']; ?>
            </td>
            <td class="small">
                <?php echo $item['contacts']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('acts', $this->fields)): ?>
            <td class="small">
                <?php echo $item['acts']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('rubrics', $this->fields)): ?>
            <td class="small">
                <?php echo $item['rubrics']; ?>
            </td>
        <?php endif; ?>
        <?php if (in_array('forms', $this->fields)): ?>
            <td class="small">
                <?php echo $item['info_catalog']; ?>
            </td>
            <td class="small">
                <?php echo $item['logo_catalog']; ?>
            </td>
            <td class="small">
                <?php echo $item['pvn_1']; ?>
            </td>
            <td class="small">
                <?php echo $item['pvn_1a']; ?>
            </td>
            <td class="small">
                <?php echo $item['pvn_1b']; ?>
            </td>
            <td class="small">
                <?php echo $item['pvn_1v']; ?>
            </td>
            <td class="small">
                <?php echo $item['pvn_1g']; ?>
            </td>
            <td class="small">
                <?php echo $item['no_exhibit']; ?>
            </td>
            <td class="small">
                <?php echo $item['info_arrival']; ?>
            </td>
        <?php endif; ?>
    </tr>
<?php endforeach; ?>