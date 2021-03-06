<?php
defined('_JEXEC') or die;
$return = ProjectsHelper::getReturnUrl();
$addUrl = JRoute::_("index.php?option=com_projects&amp;task=todo.add&amp;contractID={$this->item->id}&amp;return={$return}");
$addLink = JHtml::link($addUrl, JText::sprintf('COM_PROJECTS_TITLE_NEW_TODO'));
$printUrl = JRoute::_("https://mkv.xakepok.com/administrator/index.php?option=com_projects&amp;view=todos&amp;format=raw&amp;contractID={$this->item->id}");
$printLink = JHtml::link($printUrl, JText::sprintf('COM_PROJECTS_ACTION_PRINT'), array('target' => '_blank'));
?>
<div>
    <?php echo $addLink, " / ", $printLink; ?>
</div>
<table class="table table-striped">
    <thead>
    <tr>
        <th>
            <?php echo JText::sprintf('COM_PROJECTS_HEAD_TODO_DATE'); ?>
        </th>
        <th>
            <?php echo JText::sprintf('COM_PROJECTS_HEAD_TODO_TASK'); ?>
        </th>
        <th>
            <?php echo JText::sprintf('COM_PROJECTS_HEAD_TODO_RESULT'); ?>
        </th>
        <?php if (ProjectsHelper::canDo('projects.todos.delete')): ?>
            <th>
                <?php echo JText::sprintf('COM_PROJECTS_TITLE_REMOVE_TODO'); ?>
            </th>
        <?php endif; ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($this->todos as $todo):
        ?>
        <form action="<?php echo $todo['action']; ?>" method="post"
              id="form_task_<?php echo $todo['id']; ?>">
            <tr id="rmTodo_<?php echo $todo['id']; ?>">
                <td style="color:<?php echo ($todo['expired']) ? 'red' : 'black'; ?>">
                    <?php echo $todo['dat']; ?>
                </td>
                <td>
                    <?php echo JHtml::link(JRoute::_("index.php?option=com_projects&amp;task=todo.edit&amp;id={$todo['id']}&amp;return={$return}"), $todo['task']); ?>
                </td>
                <td class="resultTodo_<?php echo $todo['id']; ?>">
                    <?php if ($todo['state'] != 1): ?>
                        <div class="clearfix">
                            <div class="js-stools-container-bar">
                                <div class="btn-wrapper input-append">
                                    <input type="text" value="" name="result_<?php echo $todo['id']; ?>"
                                           style="width: 280px;"/>
                                    <button class="btn btn-small button-publish" style="height: 28px"
                                            onclick="closeTask(<?php echo $todo['id']; ?>); return false;"><?php echo JText::sprintf('JYES'); ?></button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($todo['state'] == 1): ?>
                        <?php echo $todo['dat'], ": ", $todo['user'], " ", $todo['result']; ?>
                    <?php endif; ?>
                </td>
                <?php if (ProjectsHelper::canDo('projects.todos.delete')): ?>
                    <td>
                        <button onclick="removeTodo(<?php echo $todo['id']; ?>); return false;"><?php echo JText::sprintf('COM_PROJECTS_TITLE_REMOVE_TODO'); ?></button>
                    </td>
                <?php endif; ?>
            </tr>
        </form>
    <?php endforeach; ?>
    </tbody>
</table>
