<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$ii = JFactory::getApplication()->input->getInt('limitstart', 0);
foreach ($this->items as $i => $item) :
    $canChange = JFactory::getUser()->authorise('core.edit.state', 'com_projects.todo.' . $item['id']);
    ?>
    <tr class="row0<?php if ($item['is_expire']) echo ' expired'; ?>">
        <td class="center">
            <?php echo JHtml::_('grid.id', $i, $item['id']); ?>
        </td>
        <td>
            <?php echo ++$ii; ?>
        </td>
        <td>
            <?php echo $item['dat_open']; ?>
        </td>
        <td>
            <?php echo $item['dat']; ?>
        </td>
        <td>
            <?php echo $item['contract']; ?>
        </td>
        <td>
            <?php echo $item['project']; ?>
        </td>
        <td>
            <?php echo $item['exp']; ?> /
            <a href="#modalCard" data-toggle="modal"
               onclick="showCard(<?php echo $item['expID']; ?>); return true;"><?php echo JText::sprintf('COM_PROJECTS_HEAD_EXP_CARD'); ?></a>
             /
            <?php echo $item['start'];?>
        </td>
        <td>
            <?php echo $item['task']; ?>
        </td>
        <td class="resultTodo_<?php echo $item['id']; ?>">
            <?php if ($item['state'] != '1'): ?>
                <div class="clearfix">
                    <div class="js-stools-container-bar">
                        <div class="btn-wrapper input-append">
                            <input type="text" autocomplete="off" id="todo_res_<?php echo $item['id']; ?>"
                                   style="width: 280px;"/>
                            <button onclick="updateTodo(<?php echo $item['id']; ?>);return false;"
                                    class="btn btn-small button-publish" style="height: 28px">OK
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($item['state'] != '0') echo $item['result']; ?>
        </td>
        <td>
            <?php echo $item['open']; ?>
        </td>
        <td>
            <?php echo $item['dat_close']; ?>
        </td>
        <?php if ($this->isAdmin): ?>
            <td>
                <?php echo $item['manager']; ?>
            </td>
        <?php endif; ?>
    </tr>
<?php endforeach; ?>