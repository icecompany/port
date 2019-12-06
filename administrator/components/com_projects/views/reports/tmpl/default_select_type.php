<?php
// Запрет прямого доступа.
defined('_JEXEC') or die;
$types = ProjectsHelper::getReportTypes();
$links = array();
foreach ($types as $type => $title)
{
    $links[] = JHtml::link(JRoute::_("index.php?option=com_projects&amp;view=reports&amp;type={$type}"), $title);
}
?>
<ul>
    <li><?php echo JHtml::link(JRoute::_("index.php?option=com_projects&view=managerstat"), JText::sprintf('COM_PROJECTS_REPORT_TYPE_BY_MANAGERS'));?></li>
    <?php foreach ($links as $link) :?>
        <li><?php echo $link;?></li>
    <?php endforeach; ?>
</ul>
