<?php
defined('_JEXEC') or die;
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldRubric extends JFormFieldList
{
    protected $type = 'Rubric';
    protected $loadExternally = 0;

    protected function getOptions()
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`id`, `title`")
            ->from("`#__prj_rubrics`")
            ->order("title");

        $view = JFactory::getApplication()->input->getString('view');
        if ($view == 'contract') {
            //Подгружаем только рубрики из текущего проекта
            $contractID = JFactory::getApplication()->input->getInt('id', 0);
            if ($contractID > 0) {
                $projectID = ProjectsHelper::getContractProject($contractID);
                $rubrics = ProjectsHelper::getProjectRubrics($projectID);
                $rubrics = implode(', ', $rubrics);
                if (!empty($rubrics)) {
                    $query->where("`id` IN ({$rubrics})");
                }
                else {
                    $query->where("`id` = 0"); //Если у проекта нет ни одной привязанной рубрики
                }
            }
        }
        else {
            if ($view !== 'project') {
                $projectID = ProjectsHelper::getActiveProject();
                if (is_numeric($projectID)) {
                    //Подгружаем только рубрики из текущего проекта
                    $rubrics = ProjectsHelper::getProjectRubrics($projectID);
                    $rubrics = implode(', ', $rubrics);
                    if (!empty($rubrics)) {
                        $query->where("`id` IN ({$rubrics})");
                    } else {
                        $query->where("`id` = 0"); //Если у проекта нет ни одной привязанной рубрики
                    }
                }
            }
        }

        $result = $db->setQuery($query)->loadObjectList();

        $options = array();

        foreach ($result as $item) {
            $options[] = JHtml::_('select.option', $item->id, $item->title);
        }

        if (!$this->loadExternally) {
            $options = array_merge(parent::getOptions(), $options);
        }

        return $options;
    }

    public function getOptionsExternally()
    {
        $this->loadExternally = 1;
        return $this->getOptions();
    }
}